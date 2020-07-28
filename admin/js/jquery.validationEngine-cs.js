

(function($) {
	$.fn.validationEngineLanguage = function() {};
	$.validationEngineLanguage = {
		newLang: function() {
			$.validationEngineLanguage.allRules = 	{"required":{    			// Add your regex rules here, you can take telephone as an example
						"regex":"none",
						"alertText":"* Toto pole je povinné",
						"alertTextCheckboxMultiple":"* Prosím vyberte jednu z možností",
						"alertTextCheckboxe":"* Toto pole je povinné"},
					"length":{
						"regex":"none",
						"alertText":"*Zadejte ",
						"alertText2":" - ",
						"alertText3": " znaků"},
					"maxCheckbox":{
						"regex":"none",
						"alertText":"* Překročena povolená kontrola"},	
					"minCheckbox":{
						"regex":"none",
						"alertText":"* Prosím vyberte ",
						"alertText2":" možnost"},	
					"confirm":{
						"regex":"none",
						"alertText":"* Vaše pole není odpovídající"},		
					"telephone":{
						"regex":"/^[0-9\-\(\)\ ]+$/",
						"alertText":"* Neplatné telefonní číslo"},	
					"email":{
						"regex":"/^[a-zA-Z0-9_\.\-]+\@([a-zA-Z0-9\-]+\.)+[a-zA-Z0-9]{2,4}$/",
						"alertText":"* Neplatná emailová adresa"},	
					"date":{
                         "regex":"/^[0-9]{4}\-\[0-9]{1,2}\-\[0-9]{1,2}$/",
                         "alertText":"* Neplatné datum datum musí bý ve formátu YYYY-MM-DD"},
					"onlyNumber":{
						"regex":"/^[0-9\ ]+$/",
						"alertText":"* Vložte prosím jen čísla"},	
					"onlyFloat":{
						"regex":"/^[-]?[0-9]+[\.]?[0-9]+$/",
						"alertText":"* Vložte prosím jen čísla / desetinná čísla (s tečkou)"},		
					"onlyFloatVarPrice":{
						"regex":"/^[0-9\ ]+$/",
						"alertText":"* Vložte prosím jen čísla / desetinná čísla (s tečkou)"},	
					"noSpecialCaracters":{
						"regex":"/^[0-9a-zA-Z]+$/",
						"alertText":"* Žádné speciální znaky nejsou povoleny povoleny"},	
					"ajaxUser":{
						"file":"validateUser.php",
						"extraData":"name=eric",
						"alertTextOk":"* Tento uživatel k dispozici",	
						"alertTextLoad":"* Čekejte prosím",
						"alertText":"* TTento uživatel je již obsazen"},	
					"ajaxName":{
						"file":"validateUser.php",
						"alertText":"* Tento uživatel je již obsazen",
						"alertTextOk":"* Toto jméno je k dispozici",	
						"alertTextLoad":"* Čekejte prosím"},		
					"onlyLetter":{
						"regex":"/^[a-zA-Z\ \']+$/",
						"alertText":"* Zadávejte prosím pouze písmena abecedy"},
					"validate2fields":{
    					"nname":"validate2fields",
    					"alertText":"* Zadejte prosím jméno a příjmení"}	
					}	
					
		}
	}
})(jQuery);

$(document).ready(function() {	
	$.validationEngineLanguage.newLang()
});