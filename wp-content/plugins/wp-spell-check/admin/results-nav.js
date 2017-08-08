jQuery(document).ready(function() {
	//Code for switching tabs on scan results page
	jQuery("#wpsc-scan-results").click(function() {
		jQuery("#wpsc-scan-results-tab").removeClass("hidden");
		if (jQuery("#wpsc-empty-fields-tab").hasClass("hidden") == false) jQuery("#wpsc-empty-fields-tab").addClass("hidden");
		
		jQuery("#wpsc-empty-fields").removeClass("selected");
		if (jQuery("#wpsc-scan-results").hasClass("selected") == false) jQuery("#wpsc-scan-results").addClass("selected");
	});
	jQuery("#wpsc-empty-fields").click(function() {
		jQuery("#wpsc-empty-fields-tab").removeClass("hidden");
		if (jQuery("#wpsc-scan-results-tab").hasClass("hidden") == false) jQuery("#wpsc-scan-results-tab").addClass("hidden");
		
		jQuery("#wpsc-scan-results").removeClass("selected");
		if (jQuery("#wpsc-empty-fields").hasClass("selected") == false) jQuery("#wpsc-empty-fields").addClass("selected");
	});
	
	var link_url = jQuery("#wpsc-empty-fields-tab .next-page").attr("href");
	link_url += "&wpsc-scan-tab=empty";
	jQuery("#wpsc-empty-fields-tab .next-page").attr("href", link_url);
	
	var link_url = jQuery("#wpsc-empty-fields-tab .last-page").attr("href");
	link_url += "&wpsc-scan-tab=empty";
	jQuery("#wpsc-empty-fields-tab .last-page").attr("href", link_url);
	
	var link_url = jQuery("#wpsc-empty-fields-tab .prev-page").attr("href");
	link_url += "&wpsc-scan-tab=empty";
	jQuery("#wpsc-empty-fields-tab .prev-page").attr("href", link_url);
	
	var link_url = jQuery("#wpsc-empty-fields-tab .first-page").attr("href");
	link_url += "&wpsc-scan-tab=empty";
	jQuery("#wpsc-empty-fields-tab .first-page").attr("href", link_url);
});