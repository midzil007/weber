<?

class module_NewsOnEmail
{		
	
	function __construct() {
		$this->db = Zend_Registry::getInstance()->db;
		$this->_tableName = 'module_NewsOnEmail';
	}
	    
    function isRegistered($email){
    	return $this->db->fetchOne("SELECT id FROM " . $this->_tableName . " WHERE email = ?", array($email));
    }
    
    function addReciever($view, $email, $fullname = '', $redir = true){
    	$validator = new Zend_Validate_EmailAddress(); 	
		if(!$validator->isValid($email)){	
			$state = 2;			    
		}
		else{
    		if($email && !$this->isRegistered($email)){
				$this->_addReciever($fullname, $email);
				$state = 1;
    		}
    		else{	
    			$state = 3;}
    		if($state ==1){
    			$ok = '&news=1';
    		
			}
		}
		if($redir){
    	 	helper_FrontEnd::redirect($view->newsUrl.'?state='.$state.''.$ok,302);
		} 
	}
	
	function _addReciever($fullname, $email){
		$data = array(
			'fullname' => $fullname, 
			'email' => $email,
			'added' => new Zend_Db_Expr('NOW()')
		);

		$this->db->insert( 
			$this->_tableName,
			$data
		);    
	} 
	
	function getNewsText($view){
		$articles = $view->articles->getArticles('dateShow', 'Desc', 0, 8, 1, 0, false, $node->nodeId);     
		$tipsNodes = $view->articles->getArticlesAsNodes($view->tree, $articles);	  	
		$fromDate = mktime(0, 0, 0, date("m")  , date("d")- 7, date("Y"));
		$fromDate = date('Y-m-d', $fromDate);
		
		
		$text = '
		<h1>Zajímavé články za minulý týden</h1>
		<p>Vážení čtenáři, za minulý týden jsme pro Vás vybrali tyto články.</p>
		<table cellspacing="4">';
		foreach ($tipsNodes as $article){
			$cArticle = $article->getPublishedContent();
			$photo = helper_FrontEnd::getResizedPhotos($cArticle->getFilesNames('photos'), $cArticle->fotoThumbName, '', 1);
			$url = Utils::getWebUrl() . helper_FrontEnd::getPath($view, $article->path);
            $date = $cArticle->getPropertyValue('dateShow');
            
            $isActual = strcasecmp($date, $fromDate); 
            
            if($isActual < 0){
            	continue; 
            }
            
            if($isActual > 6){
            	continue; 
            }
            
            $author = $cArticle->getAuthor($view);  
            
            $img = ''; 
            if($photo['path']){
            	$img = '<a href="' . $url . '"><img src="' . Utils::getWebUrl() . ($photo['path']) . '" alt=""  /></a>';  
            } 
            $text .= ' 
            	<tr>
            		<td valign="top">
            			' . $img . '
            		</td>
            		<td valign="top"> 
            			<b><a href="' . $url . '">' . $article->title . '</a></b>
            		</td>
            		<td valign="top"> 
            			' . $cArticle->getPretext(true, '','',200) . '
            		</td>
            	</tr>
            ';           
		} 		
        $text .= '</table>
        	
        <p><br /> 
        Přejeme hezký den. <br />
        <a href="http://www.modernipanelak.cz/"><img src="http://www.modernipanelak.cz/images/logo.png" alt="MODERNÍ PANELÁK" title="Zobrazit" /></a> 
        
        <br /> <br /> 
        <small>Pokud si již nepřejete dostávat novinky odpovězte na tento email.</small> 
        </p>
        ';
                 
        return $text;
	}
	
	function send($view){
		$emailText = $this->getNewsText($view); 
		$subject = 'Moderní panelák - Novinky'; 
		 		 
		$users = $this->getUsers();
		$s = Zend_Registry::getInstance()->settings;
		$emailFrom = $s->getSettingValue('outcommingEmail'); 
		$emailFromName = $s->getSettingValue('outcommingEmailName');
		
		foreach ($users as $usr) { 
			
			$emailText2 = $emailText1 = $emailText;
			$mail = new Email();					
			$text = Helpers::prepareEmail($mail, $emailText, false, false, '007294', '262626');			
			$mail->setBodyText(strip_tags($emailText)); 
			$mail->setBodyHtml(urldecode($text));		
			$mail->setSubject($subject); 
			$mail->setFrom($emailFrom, $emailFromName);					 	 
			
			$mail->addTo($usr['email'], $usr['fullname']); 
			// $mail->addTo('orlik.michal@gmail.com', 'misan'); $mail->send();	die('ok');   
			$mail->addToQueue();			   
		} 
		// pr($users); 
	} 
	
	function getUsers(){
    	return $this->db->fetchAll("SELECT * FROM " . $this->_tableName . "");
    }
    
    function import(){
    	$data = array(
	    	'Roman;Hořák;rh.js@seznam.cz',
			'Martin;Košák;martinkosak@seznam.cz',
			'Ladislav;Samek;ladislav.samek@illbruck.cz',
			'Radka;Kaňová;ZugarovaRadka@seznam.cz',
			'Miloslav;Křišta;mkslava@centrum.cz',
			'Pavel;Vraník;vranik.p@seznam.cz',
			'Martina;Applová;applova.martina@seznam.cz',
			'Dagmar;Čechová;dag.cechova@seznam.cz',
			'Radek;Halada;r.halada@seznam.cz',
			'Martina;Svobodová;Enfante@seznam.cz',
			'Zdeněk;Gregor;z.gregor@ktvm.cz',
			'Taťána;Těžká;t.tezka@seznam.cz',
			'Dagmar;Holá;dagmar.hola@seznam.cz',
			'Radoslava;Lacinová;Radoslavalacinova@seznam.cz',
			'Darja;Hubáčková;darja.hubackova@seznam.cz',
			'Radek;Hudec;hudec@ebrana.cz',
			'Dagmar;Matoušková;matouskova@uuo.cz',
			'Zdeňka;Ruckerová;zd.rucker@email.cz',
			'Bibiana;Černecká;bibiana8480@azet.sk',
			'Jana;Janušová;jana_janusova@centrum.cz',
			'Helena;Kučerová;helenakucerova@email.cz',
			'Barbora;Karásková;barbora.karaskova@centrum.cz',
			'Roman;;kukuckovi@gmail.com',
			'Libuše;Vlková;libuska.vlcice@seznam.cz',
			'Michaela;;mika.otaska@seznam.cz',
			'Zdenka;kovránková;skovrankovazdenka@seznam.cz',
			'Anna;Berkyová;berkyova.anna@seznam.cz',
			'Petr;;head4@seznam.cz',
			'Hana;Kaplanová;hanakaplanova@seznam.cz',
			'Radek;Schmidt;erzin@seznam.cz',
			'Anna;Hájková;h.anna@tiscali.cz',
			'Barbora;Vlčková;vlckovabarbora@seznam.cz',
			'Jaromír;Machač;distribution@avex.cz',
			'Vladimír;Mešťánek;vmestanek@seznam.cz',
			'Věra;Čiháková;nurseV@seznam.cz',
			'Ladislava;Brabencová;brabencova@jihlavske-listy.cz',
			'Josef;Čásenský;casensky@seznam.cz',
			'Helena;;heert@seznam.cz',
			'Silvie;Pietraszova;PIETRASZOVA@SEZNAM.CZ',
			'Curych;Petr;zeneva@centrum.cz',
			'Jan;Chára;j.chara@panelplus.cz',
			';Kotrbatá;l.kotrbata@email.cz',
			'Iveta;Maršounová;ivetamarsounova@seznam.cz',
			'Stanislava;Handšuchová;stanickahansuchova@seznam.cz',
			'Petra;Kányai;kanyai@seznam.cz',
			'Pavel;Hučala;phucala@seznam.cz',
			'Michala;Janků;sladomelova1@seznam.cz',
			'Jan;Yakuza;yakuza10@seznam.cz',
			'Lenka;Dvořáková;dvorkal@seznam.cz',
			'Gabriela;Hornová;hornovagabriela@seznam.cz',
			'Pavlína;Schánělcová;PavlinaSchanelcova@seznam.cz',
			'Veronika;Pařízková;parizkova@voda-jvs.cz',
			'Jiří;Haška;jirihaska@seznam.cz',
			'Jana;Mackerlova;mackerlovaj@seznam.cz',
			'Radim;Hlavička;radim.hlavicka@cmi.cz',
			'Petr;Zámecký;zameckyp@email.cz',
			'Lucie;Bružková;l.bruzkova@seznam.cz',
			'Lada;Bartošová;ladabartosova@seznam.cz',
			'Alonka;;fmklimova@seznam.cz',
			'Petr;Haupt;petr.haupt@centrum.cz',
			'Marie;tefucová;mariestefucova@seznam.cz',
			'Zdenka;Nebáznivá;zdenkane@post.cz',
			'Miloslav;Pirkl;milos.pirkl@seznam.cz',
			'Lenka;Semová;lenka.semova@seznam.cz',
			'Michal;Nachtnebel;nightfog@seznam.cz',
			'Jarous;;jarous.1@centrum.cz',
			'Alena;Zapadlová;zapadlovaa@seznam.cz',
			'Lenka;Kožichová;kozic-le@seznam.cz',
			'Marek;Vincour;marek.vincour@seznam.cz',
			'Karel;Běták;betak@rcp.bno.cz',
			'Tomáš;Pešek;tompesik@seznam.cz',
			'Zoubková;;zzinternet@seznam.cz',
			'Ilona;Sládková;sladkovailona@seznam.cz', 
			'Tom300;;k1team@email.cz',
			'Petr;Tichý;tichy-petr@quick.cz', 
			'Martin;Čedík;pseda205@seznam.cz',
			'Lukáš;Zdechovan;lukas@zdechovan.com',
			'Petr;Kudera;kudera.petr@seznam.cz',
			'Jiří;Soukup;juras.plavec@seznam.cz',
			'Jakuba;Ondřej;jake.jakuba@seznam.cz',
			'Jaroslav;Sterba;jaroslav.sterba@tiscali.cz',
			'Lenka;Kokešová;kokesova@realmost.cz',
			'Ing. Lubomír;Hlávka;l.hlavka@seznam.cz',
			'Slávek;;piki2001@seznam.cz',
			'Ing. Jindřich;Vinzens;kuba.hrdina@seznam.cz',
			'Tomáš;Pichrt;pichi@email.cz', 
			'Radek;Červenka;grafitkt@seznam.cz',
			'Jana;Dvořáková;cauca1@seznam.cz',
			'Jana;Zichová;zichovi@iol.cz',
			'Jana;Roznětinská;jtinska@seznam.cz',
			'Miriam;Živorová;miriam.zivorova@ovajih.cz',
			'Václav;Holenda;vholenda@multima.cz',
			'Jaromír;Tomiczek;tomiczej@seznam.cz',
			';Petrýdesová;j.petrydesova@seznam.cz',
			'Jan;Závěta;BEDAZAVETA@email.cz'
    	);
    	
    	foreach ($data as $user){ 
    		$parts = explode(';', $user); 
    		e($parts);  
    		$this->_addReciever($parts[0] . ' ' . $parts[1], $parts[2]);
    	}
    }    
}

?>