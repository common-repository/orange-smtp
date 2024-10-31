       /*=== SMTP TOOLS select ==== */ 

        jQuery(document).ready(function() {
            var smtpbutton = jQuery('input[type="radio"][name="smtp_service[smtp_service_tools]"]');
            var default_callback = jQuery('#default_callback');
            var gmail_callback = jQuery('#gmail_callback');
            var gmail_sender = jQuery('#gmail_sender');
            var mailgun_callback = jQuery('#mailgun_callback');
            var other_callback = jQuery('#other_callback');
            var sendgrid_callback = jQuery('#sendgrid_callback');
            var postmark_callback = jQuery('#postmark_callback');
            var sparkpost_callback = jQuery('#sparkpost_callback');
            var smtp_callback = jQuery('#smtp_callback');
    
            function onclickchangebutton() {
                default_callback.hide();
                gmail_callback.hide();
                mailgun_callback.hide();
                other_callback.hide();
                sendgrid_callback.hide();
                postmark_callback.hide();
                sparkpost_callback.hide();
                smtp_callback.hide();
                gmail_sender.show();

                if (this.value === 'default') {
                        default_callback.show();
                    } else if (this.value === 'gmail') {
                        gmail_callback.show();
                    } else if (this.value === 'other') {
                        other_callback.show();
                    } else if (this.value === 'mailgun') {
                        mailgun_callback.show();
                    } else if (this.value === 'sendgrid') {
                        sendgrid_callback.show();
                    } else if (this.value === 'postmark') {
                        postmark_callback.show();
                    } else if (this.value === 'sparkpost') {
                        sparkpost_callback.show();
                    } else if (this.value === 'smtp') {
                        smtp_callback.show();
                }
            }

            smtpbutton.on('change', onclickchangebutton);
            // Initially, check the selected radio button and handle the change
            smtpbutton.each(function() {
                if (jQuery(this).prop('checked')) {
                    onclickchangebutton.call(this);
                }
            });
        });

    
     /*=== Test email advacne field show hide. ==== */
    
    jQuery(document).ready(function(){
        jQuery("#Advancedsettings").click(function(){
            jQuery(".advanced-settings").toggle();
            jQuery(this).text(function(i, text){
            return text === "Show Advanced Settings" ? "Hide Advanced Settings" : "Show Advanced Settings";
          });
        });
    });  


    /*==== Datatable integaret =====.*/ 

    jQuery(document).ready(function () {
        jQuery('#organgetable').DataTable();

    });


    /*==== Message Delete Confirmation  =====.*/ 
    
     function confirmation(){
        var result = confirm("Are you sure to delete?");
        return result;
     }


    /*==== Pro veriosn indicate =====*/ 

    jQuery(document).ready(function() {
        jQuery('#orangesmtp21, #orangesmtp5, #orangesmtp11, #orangesmtp17').click(function() {
            jQuery('#orange_overlay').fadeIn(600);
            jQuery('#orange_popup').fadeIn(600);
        });
    
        jQuery('#closePopup').click(function() {
            jQuery('#orange_overlay').fadeOut(600);
            jQuery('#orange_popup').fadeOut(600);
        });
    });


    /*==== email tempalate =====*/ 

    jQuery(document).ready(function() {

        for (let i = 1; i <= 4; i++) {
            jQuery(`#orangetemplate-${i}`).hover(
            function() {
                jQuery(`#orange_infoText-${i}`).fadeIn(500);
            },
            function() {
                jQuery(`#orange_infoText-${i}`).fadeOut(500);
            }
            );
        }      
    });

    