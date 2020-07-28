<?

class module_Events
{
				
	function __construct() {
		$this->db = Zend_Registry::getInstance()->db;		
	}
		
	function days_in_month($month, $year) { 
	 return date('t', mktime(0, 0, 0, $month, 1, $year)); 
	}

	function generateDays( $month, $year ){
		$days = array();		
		$day_now = date('d') ;
		$month_now = date('m') ;
		$year_now = date('Y') ;

		//the first day of the month
		$first_day = mktime(0,0,0,$month, 1, $year) ;
		$this->monthTitle = Utils::$months[$month-1];

		$day_of_week = date('w', $first_day) ; // 0 (pro neděli) až 6 (pro sobotu)
		
		$blank = $day_of_week==0?6:$day_of_week-1; 
		//We then determine how many days are in the current month
		$days_in_month = $this->days_in_month($month, $year) ;

		//This counts the days in the week, up to 7
		$day_count = 1;

		//first we take care of those blank days
		while ( $blank > 0 )
		{
			$days[] = 0;
			$blank = $blank-1;
		}

		$day_num = 1;
		//count up the days, untill we've done all of them in the month
		while ( $day_num <= $days_in_month )
		{
			$days[] = array( 
				'dayNo' => $day_num, 
				'date' => $year . '-' . $month . '-'. $day_num
			);
			
			$day_num++;
		}
		
		return $days;		
	}
	
	function getNextMonth($month = 0, $year = 0){
		if(!$month){
			$month = date('m') ;
			$year = date('Y') ;
		}
		
		$nextMonth = mktime (0, 0, 0, $month + 1, 1 , $year);
		return array(
			'm' => date('n', $nextMonth), 
			'y' => date('Y', $nextMonth)
		);
	}
	
	function getPrevMonth($month = 0, $year = 0){
		
		if(!$month){
			$month = date('n') ;
			$year = date('Y') ;
		}
		
		$prevMonth = mktime (0, 0, 0, $month - 1, 1, $year);
		return array(
			'm' => date('n', $prevMonth), 
			'y' => date('Y', $prevMonth)
		);
	}
	
	function getEventsByParent($parentId){  
		
		return $this->db->fetchAll(
				"SELECT * FROM `module_Events` WHERE parent = ?",
				$parentId
			);
	}
	
	function createCalendar( $month = 0, $year=0 ){
		if(!$month){
			$month = date('m') ;
		} else {
			if($month < 10){
				$month = '0'.$month;
			}			
		}
		if(!$year){
			$year = date('Y') ;
		}
		$today  = date('Y-m-j');		
		$days = $this->generateDays($month, $year);
		//pr($days);
		
		$weekdays = array(
			5,6,12,13,19,20,26,27,33,34 
		);
		$news = new module_NewsWeb();		
		foreach ($days as $i => $day){
			
			$event = $news->getNews('title', 'asc', 0, 1, $day['date']);
			
			// echo"SELECT * FROM `module_Events` WHERE status=? AND ( ? between `startAt` AND `within` )"; 
			 
			if(count($event)){  
				$e = $event[0];  
				$days[$i]['event'] = $e;   
			}
			if( $days[$i]['date'] == $today){				
				$days[$i]['isToday'] = true;
			}			
			
			if(in_array($i, $weekdays )){				
				$days[$i]['isWeek'] = true;
			}	 
		} 
		
		// pr($days);		
		return $days;
	}
	
	function getEvents( $timeCondition = '', $sort = 'startAt', $sortType = 'asc', $all = true, $month = 0, $year = 0, $type = 0 ){
		$op = '>=';
		switch ($timeCondition){
			case 'all';
				$date = '1999-01-01';
				break;
			case 'specific':
				if(!$month){
					$month = date('m') ;
					$year = date('Y') ;
				}
				$date = "$year-$month-01";
				break;
			case 'future':
				$month = date('m') ;
				$year = date('Y') ;
				$day = date('d') ;
				
				$date = "$year-$month-$day";
				//e($date);
				break;
			case 'passed':
				$date = new Zend_Db_Expr('CURDATE()');	
				$op = '<';
				break;
			default:
				$date = new Zend_Db_Expr('CURDATE()');
		}
		
		$sortType = $sortType?$sortType:'Desc';
		$select = Zend_Registry::getInstance()->db->select();  
		$select->from('module_Events', '*')
		       ->where('`startAt` ' . $op .' ? ', $date)
		       ->where('`status` = ?', '1')
		       ->order( $sort . ' ' . $sortType);
		        
		if($type && $type != '-'){   
			$select->where('`type` = ?', $type);
		}       
		//e($select->__toString());
		       
		$events = $this->db->fetchAll($select);
		
		if(count($events)){
			/*
			foreach ($events as $i => $e){
				$events[$i] = new Event
					(
						$e['id'],
						$e['title'],
						$e['detail'],
						$e['startAt'],
						$e['within'],
						$e['type']
					);				
			}
			*/
			$eventsTable = array();
			$eTypes = $this->getEventTypes();
			foreach ($events as $i => $e){
				if($e['startAt'] == $e['within']){
					$date = Utils::formatDate($e['startAt']);
				} else {			
					$date = Utils::formatDate($e['startAt']).' - ' . Utils::formatDate($e['within']);			
				}
				
				if($all){
					$eventsTable[$i] = $e;
				} else {
					$eventsTable[$i] = array
						(
							$e['id'],
							$e['title'], 
							$date,
							$eTypes[$e['type']]
						);	
				}
			}
		}	
		return $eventsTable;
	}

	function getEventTypes(){
		$types = Zend_Registry::getInstance()->config->modules->events->types;		
		return $types?$types:Event::$types;
	}
	function getEventTypesColors(){ 
		$types = Zend_Registry::getInstance()->config->modules->events->typesColors;		
		return $types?$types:Event::$colors;
	}
	
	
	
	function addEvent( $id, $title, $detail, $startAt, $within, $type, $photos = '', $parent = 0, $startTime = '', $endTime = '', $url = ''  ){		
		$e = new Event( $id, $title, $detail, $startAt, $within, $type, $photos, $parent, $startTime, $endTime, $url );
		$e->save(); 
		return $e;
	}
	 
	function updateEvent( $id, $title, $detail, $startAt, $within, $type, $photos = '', $parent = 0, $startTime = '', $endTime = '', $url = ''  ){		
		$e = new Event( $id, $title, $detail, $startAt, $within, $type, $photos , $parent, $startTime, $endTime, $url );
		$e->update();
		return $e;  
	}  
	
	function initEvent( $id ){		
		$e = new Event($id);
		return  $e->init();
	}
	
	function deleteEvent( $id ){		
		$e = new Event($id);
		$e->init();
		$e->delete();
	}
	
	function getEvent( $id ){		
		$e = new Event($id);
		$e->init();
		return $e;
	}
	
	function getInitializedEvent($id){
		$event = Zend_Registry::getInstance()->db->fetchRow(
			'SELECT * FROM module_Events WHERE id=?',
			array($id)
		);
		
		return new Event($event['id'], $event['title'], $event['detail'], $event['startAt'], $event['within'], $event['type'], $event['photos'], $event['parent'], $event['startTime'], $event['endTime'], $event['url']);
	}
	
	function getRepeats(){
		return array(
			0 => 'Jednorázová událost',
			1 => 'Každý den',  
			2 => 'Každý všední den',
			3 => 'Každý týden',
			4 => 'Každých 14 dní'
		);
	}
	
	function getDates($start, $repeat){
		
		// generuju na pul roku dopredu
		$days = 183;
		
		list($y, $m, $d) = explode('-', $start);
		$dates = array(); 
		switch ($repeat){
			case 1:
				for($i = 1; $i <= $days; $i++){
					$time = mktime(0, 0, 0, $m, $d + $i, $y);	 
					$date = date('Y-m-d', $time); 
					$dates[$date] = $date;
				}
				break; 
			case 2: 
				for($i = 1; $i <= $days; $i++){
					$time = mktime(0, 0, 0, $m, $d + $i, $y);					
					$dayInWeek = date('N', $time);
					if($dayInWeek == 6 || $dayInWeek == 7){
						continue;
					}
					$date = date('Y-m-d', $time); 
					$dates[$date] = $date;
				} 
				break;
			case 3: 
				$weeks = ceil($days/7);
				for($i = 1; $i <= $weeks; $i++){
					$time = mktime(0, 0, 0, $m, $d + ($i * 7), $y);	 
					$date = date('Y-m-d', $time); 
					$dates[$date] = $date;
				}  
				break; 
			case 4: 
				$weeks2 = ceil($days/14);
				for($i = 1; $i <= $weeks2; $i++){
					$time = mktime(0, 0, 0, $m, $d + ($i * 14), $y);	 
					$date = date('Y-m-d', $time); 
					$dates[$date] = $date;
				}   
				break; 
		}
		return $dates;
	}
}

class Event {
	public $id;
	public $title;
	public $detail;
	public $startAt; // 2007-09-28
	public $within;
	public $type, $photos;
	
	public $fotoFullName = 'rFull';
    public $fotoThumbName = 'rThumb';
    public $fotoMiniName = 'rMini';
    public $fotoMiniName2 = 'rMiniOverview';
     
	public static $types = array(
		'-' => ' vyberte typ ', 
		'bat' => 'Batolata',
		'det' => 'Děti 3-6',	
		'pre' => 'Předškoláci',
		'dos' => 'Dospělí',
		'set' => 'Senioři',
		'aqu' => 'Aqua aerob.', 
		'e1' => 'Akce - modrá',
		'e2' => 'Akce - zelená',
		'e3' => 'Akce - žlutá',
		'e4' => 'Akce - červená',
		'e5' => 'Akce - oranžová'
	);
	
	public static $colors = array(
		'bat' => 'c198cf',
		'det' => 'bdc700',	
		'pre' => 'fed000',
		'dos' => 'f29400',
		'set' => '00aad2',
		'aqu' => 'e2007a',
		'e1' => 'c1f2fe',
		'e2' => '5be351', 
		'e3' => 'fff3bf',
		'e4' => 'ffadb3', 
		'e5' => 'ffc66c'
	);
	
	function __construct( $id = 0, $title = '', $detail = '', $startAt = '', $within = '', $type = '', $photos = '', $parent = 0, $startTime = '', $endTime = '', $url = '' ) {
		$this->id = $id;
		$this->title = $title;
		$this->detail = Utils::getWYSIWYGHtml($detail);
		$this->startAt = $startAt; 
		$this->within  = $within;
		$this->type  = $type;
		$this->photos  = $photos;
		$this->parent  = $parent;
		$this->startTime  = $startTime;
		$this->endTime  = $endTime;
		$this->url  = $url;
		
		if( !$this->within || $this->within == '0000-00-00' ){
			$this->within = $this->startAt;
		}
	}
	
	function isActual(){
		if(strcasecmp($this->startAt, date('Y-m-d')) >= 0){
			return true;
		} else {
			return false;
		}
	}
	
	function init(){
		$event = Zend_Registry::getInstance()->db->fetchRow(
			'SELECT * FROM module_Events WHERE id=?',
			array($this->id)
		);  
		return $event; 
	}

	function save(){
		//insert
		$data = array(
			'title' => $this->title,
			'detail' => $this->detail,
			'startAt' => $this->startAt,
			'within' => $this->within,
			'parent' => $this->parent,
			'startTime' => $this->startTime,
			'endTime' => $this->endTime,
			'url' => $this->url,  
			'type' => $this->type
		);
		
		if($this->photos){ 
			$data['photos'] = $this->photos;
			$this->createFiles();
		}
		
		Zend_Registry::getInstance()->db->insert('module_Events',$data);
		$this->id = Zend_Registry::getInstance()->db->lastInsertId();
	}  
	
	function update(){
		$data = array(
			'title' => $this->title,
			'detail' => $this->detail,
			'startAt' => $this->startAt,
			'within' => $this->within,  
			'parent' => $this->parent,  
			'startTime' => $this->startTime,
			'endTime' => $this->endTime, 
			'url' => $this->url, 
			'type' => $this->type
		);
		if($this->photos){
			$data['photos'] = $this->photos;
			$this->createFiles();
		}
		
		Zend_Registry::getInstance()->db->update('module_Events',$data,  'id = ' . $this->id);
	}
	
	function delete(){	
		Zend_Registry::getInstance()->db->update(
			'module_Events',
			array(	'status' => '0' ), 
			Zend_Registry::getInstance()->db->quoteInto(" id=? ", $this->id)
		);
	}
	
	function __toString() {
		pr($this);
	}
	 
	function createFiles(){
		$fakeContent = new Content();
       	$autosize = false;
    	$fakeContent->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 640,
    				'height' => 480,
    				'autosize' => $autosize  
    			),
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 220,
    				'height' => 165,
    				'autosize' => $autosize 
    			),
    			array(
    				'name' => $this->fotoMiniName, 
    				'width' => 70,
    				'height' => 52,
    				'autosize' => $autosize
    			),
    			array(
    				'name' => $this->fotoMiniName2, 
    				'width' => 70,
    				'autosize' => $autosize
    			)
    		), 
    		'photos',
    		$this->photos
    	);
    }
}

?>