jQuery(document).ready(function() {
	//Code for switching tabs on scan results page
	jQuery("#wpsc-general-options").click(function() {
		jQuery("#wpsc-general-options-tab").removeClass("hidden");
		if (jQuery("#wpsc-scan-options-tab").hasClass("hidden") == false) jQuery("#wpsc-scan-options-tab").addClass("hidden");
		if (jQuery("#wpsc-empty-options-tab").hasClass("hidden") == false) jQuery("#wpsc-empty-options-tab").addClass("hidden");
		
		jQuery("#wpsc-scan-options").removeClass("selected");
		jQuery("#wpsc-empty-options").removeClass("selected");
		if (jQuery("#wpsc-general-options").hasClass("selected") == false) jQuery("#wpsc-general-options").addClass("selected");
		
		jQuery(".wpsc-nav-tab").attr("value", "general");
	});
	jQuery("#wpsc-scan-options").click(function() {
		jQuery("#wpsc-scan-options-tab").removeClass("hidden");
		if (jQuery("#wpsc-general-options-tab").hasClass("hidden") == false) jQuery("#wpsc-general-options-tab").addClass("hidden");
		if (jQuery("#wpsc-empty-options-tab").hasClass("hidden") == false) jQuery("#wpsc-empty-options-tab").addClass("hidden");
		
		jQuery("#wpsc-general-options").removeClass("selected");
		jQuery("#wpsc-empty-options").removeClass("selected");
		if (jQuery("#wpsc-scan-options").hasClass("selected") == false) jQuery("#wpsc-scan-options").addClass("selected");
		jQuery(".wpsc-nav-tab").attr("value", "scan");
	});
	jQuery("#wpsc-empty-options").click(function() {
		jQuery("#wpsc-empty-options-tab").removeClass("hidden");
		if (jQuery("#wpsc-general-options-tab").hasClass("hidden") == false) jQuery("#wpsc-general-options-tab").addClass("hidden");
		if (jQuery("#wpsc-scan-options-tab").hasClass("hidden") == false) jQuery("#wpsc-scan-options-tab").addClass("hidden");
		
		jQuery("#wpsc-general-options").removeClass("selected");
		jQuery("#wpsc-scan-options").removeClass("selected");
		if (jQuery("#wpsc-empty-options").hasClass("selected") == false) jQuery("#wpsc-empty-options").addClass("selected");
		jQuery(".wpsc-nav-tab").attr("value", "empty");
	});
	
	jQuery("#check-all").click(function() {
		if (jQuery(this).is(':checked')) {
			jQuery('#wpsc-scan-options-tab input:not(#check-all):not(.ignore-check-all)').prop('checked',true);
		} else {
			jQuery('#wpsc-scan-options-tab input:not(#check-all):not(.ignore-check-all)').prop('checked',false);
		}
	});
	
	jQuery("#check-all-empty").click(function() {
		if (jQuery(this).is(':checked')) {
			jQuery('#wpsc-empty-options-tab input:not(#check-all-empty):not(.ignore-check-all)').prop('checked', true);
		} else {
			jQuery('#wpsc-empty-options-tab input:not(#check-all-empty):not(.ignore-check-all)').prop('checked', false);
		}
	});
});