$(document).ready(function(){

	var confirmMessage = 'Your feedback was submitted succesfully.';
	var errorMessage   = 'There was an error submitting your feedback. Please try again later.';

    //Slider object
    var slider = $('#feedbackit-slideout,#feedbackit-slideout_inner');

	/*
	Hide all on IE < 9 OR Firefox <= 3.5
	*/
	if( (get_browser() == 'MSIE' && get_browser_version() <= 9) || (get_browser() == 'Firefox' && get_browser_version() <= 3.5)){
        slider.css( "display", 'none');
    }else{
        slider.css( "display", 'block');
	}

	/*
	Submit button click
	 */
	$('#feedbackit-form').submit(function(e){

		//Hide feedback slider
        slider.hide();

		html2canvas(document.body, {
		  onrendered: function(canvas) {
		    
		    //encode the image data as a base64 encoded PNG file and return it
		    var strDataURI = canvas.toDataURL(); 

		    //Serialize the feedback form
		    var postData = $('#feedbackit-form').serializeArray();

		    //Add screenshot
		    postData.push({name:'screenshot',value:strDataURI});

		    //Add current URL
		    postData.push({name:'url',value:document.URL});

		    //Add browser
		    postData.push({name:'browser',value:get_browser()});

		    //Add browser version
		    postData.push({name:'browser_version',value:get_browser_version()});

            //Add OS Flavor
            postData.push({name:'os',value:get_os_flavor()});

            //Select elements:
            var modaltitle = $('#feedbackit-modal .modal-title');
            var modalbody  = $('#feedbackit-modal .modal-body');

		    //Ajax call to controller to save this feedback report
		    $.ajax(
		    {
		        url : window.formURL, //Use url created in Element
		        type: "POST",
		        data : postData,
		        success:function(message, textStatus, jqXHR) 
		        {
		        	closeandreset();

		        	/*
		        	Only use modal if TwitterBootstrap Javascript is loaded
		        	 */
		        	if( $.isFunction( $.fn.modal ) ){
                        modaltitle.html('Feedback submitted');
                        modalbody.html(message);
		            	$('#feedbackit-modal').modal('show');
		        	}else{
		        		alert(confirmMessage);
		        	}
		        	
		        },
		        error: function(jqXHR, textStatus, errorThrown) 
		        {
		        	//Check for error messages
		        	if(jqXHR.responseText != ''){
		        		errorMessage = jqXHR.responseText;
		        	}

		        	/*
		        	Only use modal if TwitterBootstrap Javascript is loaded
		        	 */
		        	if( $.isFunction( $.fn.modal ) ){
                        modaltitle.html('Error');
                        modalbody.html(errorMessage);
		            	$('#feedbackit-modal').modal('show');
		        	}else{
		        		alert(errorMessage);
		        	}
		        }
		    });
		    
		    //Show it again
              slider.show();
		  }
		});

		e.preventDefault();
	});

	/*
	Hightlight button click
	 */
	$('#feedbackit-highlight').mouseup(function(){

		//Fadeout highlight if any
		$("#feedbackit-highlight-holder").fadeOut();

		$(this).queue(function() {
	       
	       	//Disable button and show loading text
	       	try{
				$('#feedbackit-highlight').button('loading');
			}catch(err){
				console.log(err.message + ' This requires TwitterBootstrap js to be loaded.');
			}

            //Change cursor:
            $( "body" ).css( "cursor", 'crosshair');

	        $(this).dequeue();

	    }).delay(250).queue(function() {
	        
	        /*
			Highlight function
			 */
			$('body').mousedown(function(e){
				// capture the mouse position
			    var posx = 0;
			    var posy = 0;
			    if (!e) var e = window.event;
			    if (e.pageX || e.pageY)
			    {
			        posx = e.pageX;
			        posy = e.pageY;
			    }
			    else if (e.clientX || e.clientY)
			    {
			        posx = e.clientX;
			        posy = e.clientY;
			    }
			 
			 	//Set position, to exactly center substract half the width and height from the x and y position
                var highlightholder = $( "#feedbackit-highlight-holder" );
                highlightholder.css( "left", posx - 75);
                highlightholder.css( "top", posy - 75);
                highlightholder.fadeIn();

				//Reset highlight button
				try{
					$('#feedbackit-highlight').button('reset');
				}catch(err){
					console.log(err.message + ' This requires TwitterBootstrap js to be loaded.');
				}

                //Reset cursor:
                $( "body" ).css( "cursor", 'default');

				//Unbind click function
				$('body').off('mousedown');

				e.preventDefault();
			});

	        $(this).dequeue();

	    });
	});

	/*
	Confirm terms checkbox changed
	 */
	$('#feedbackit-okay').change(function(){
        //Run the update submit button code
        updateSubmitButton();
	});

    /*
     Confirm send copy checkbox changed
     */
    $('#feedbackit-copyme').change(function(){

        var emailfield = $("#feedbackit-email");
        var namefield = $("#feedbackit-name");

        if( $(this).is(':checked') ){
            //Add required attribute
            emailfield.attr("required", "required");
            namefield.attr("required", "required");
            return;
        }

        //Remove required attribute
        emailfield.removeAttr("required");
        namefield.removeAttr("required");
    });

	/*
	Detect browser
	 */
	function get_browser(){
	    var N=navigator.appName, ua=navigator.userAgent, tem;
	    var M=ua.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
	    if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
	    M=M? [M[1], M[2]]: [N, navigator.appVersion, '-?'];
	    return M[0];
    }

    /*
	Detect browser version
	 */
	function get_browser_version(){
	    var N=navigator.appName, ua=navigator.userAgent, tem;
	    var M=ua.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
	    if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
	    M=M? [M[1], M[2]]: [N, navigator.appVersion, '-?'];
	    return M[1];
    }

    /*
    Detect OS flavor
     */
    function get_os_flavor(){

        // This script sets OSName variable as follows:
        // "Windows"    for all versions of Windows
        // "MacOS"      for all versions of Macintosh OS
        // "Linux"      for all versions of Linux
        // "Unix"       for all other Unix flavors
        // "Unknown OS" indicates failure to detect the OS

        var OSName="Unknown OS";
        if (navigator.appVersion.indexOf("Win")!=-1) OSName="Windows";
        if (navigator.appVersion.indexOf("Mac")!=-1) OSName="MacOS";
        if (navigator.appVersion.indexOf("X11")!=-1) OSName="Unix";
        if (navigator.appVersion.indexOf("Linux")!=-1) OSName="Linux";

        return OSName;
    }

    /*
	Click on closed feedback tab
	 */
    $("#feedbackit-slideout").click(function(){
		//Open menu 
		$("#feedbackit-slideout").addClass("feedbackit-slideout_outer");
		$("#feedbackit-slideout_inner").addClass("feedbackit-slideout_inner");  
	});

	/*
	Click on cancel button
	 */
	$("#feedbackit-cancel").click(function(){
      	closeandreset();
	});	

	/*
	Close and reset function
	 */
	function closeandreset(){
		//Close menu 
      	$("#feedbackit-slideout").removeClass("feedbackit-slideout_outer");
      	$("#feedbackit-slideout_inner").removeClass("feedbackit-slideout_inner"); 

      	//Reset fields
      	$('.feedbackit-input').val(''); //Reset
      	$('#feedbackit-okay').attr('checked', false);
        $('#feedbackit-copyme').attr('checked', false);
      	$("#feedbackit-submit").attr("disabled", "disabled");
		$("#feedbackit-highlight-holder").fadeOut();
	}

    /*
    Function to update the submit button (based on terms checkbox)
     */
    function updateSubmitButton(){

        var submitbutton = $("#feedbackit-submit");

        if( $('#feedbackit-okay').is(':checked') ){
            //Enable button
            submitbutton.removeAttr("disabled");
            return;
        }

        //Disable button
        submitbutton.attr("disabled", "disabled");
    }

	/*
	Activate tooltip for screenshot approval
	 */
	if( $.isFunction( $.fn.modal ) ){ //This is still quite dirty. For some reason the tooltip function is always loaded, even if not the TB tooltip. So we check for modal which is TB only
		$('#feedbackit-okay-message').tooltip();
	}

    //Run update submit button to set starting state (Based on terms checkbox state)
    updateSubmitButton();
});