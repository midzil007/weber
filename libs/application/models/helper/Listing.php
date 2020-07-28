<?
/**
 * Listing
 * @package helper
 */
 
class helper_Listing {
    		
	static function init( $view ){		    	
    	$view->listingPerPage = $view->listingPerPage?$view->listingPerPage:7;
		$view->listingCurentPage = $view->inputGet->page;
		$view->listingCurentPage = $view->listingCurentPage?$view->listingCurentPage:0;
		
		if(!$view->ListingItemsCount){
			$view->ListingItemsCount = count($view->children);
		}
		
		$view->ListingPagesCount = ceil($view->ListingItemsCount / $view->listingPerPage);
		
		$view->listingNextPage = $view->listingNextPageShowTo = $view->listingCurentPage + 1;
		
		$view->listingNextPage = min($view->listingNextPage, $view->ListingPagesCount - 1);
		
		
		if($view->listingNextPage < 0){
			$view->listingNextPage = 0;
		}
		
		$view->listingPrevPage = $view->listingCurentPage - 1;
		$view->listingPrevPage = max($view->listingPrevPage, 0);	
		
		$view->listingSQLStartPos = ($view->listingCurentPage) * $view->listingPerPage;
				
    }
    
    // pokud jsou 2 listingy na strance
    static function init2( $view ){		    	
    	$view->listingPerPage2 = $view->listingPerPage2?$view->listingPerPage2:7; 
		$view->listingCurentPage2 = $view->inputGet->page2;
		$view->listingCurentPage2 = $view->listingCurentPage2?$view->listingCurentPage2:0;
		
		if(!$view->ListingItemsCount2){
			$view->ListingItemsCount2 = count($view->children);
		}
		
		$view->ListingPagesCount2 = ceil($view->ListingItemsCount2 / $view->listingPerPage2);
		
		$view->listingNextPage2 = $view->listingNextPageShowTo2 = $view->listingCurentPage2 + 1;
		
		$view->listingNextPage2 = min($view->listingNextPage2, $view->ListingPagesCount2 - 1);
		
		
		if($view->listingNextPage2 < 0){
			$view->listingNextPage2 = 0;
		}
		
		$view->listingPrevPage2 = $view->listingCurentPage2 - 1;
		$view->listingPrevPage2 = max($view->listingPrevPage2, 0);	
		
		$view->listingSQLStartPos2 = ($view->listingCurentPage2) * $view->listingPerPage2;
				
    }
    
}
?>
