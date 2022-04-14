/*
 * Either Load the homepage with all options
 */
function load_the_page()
{
	var cookie_status = cookie_check();
	if(cookie_status != "bad_cookie")	load_home();
	else								load_login_prompt();
	
}

//Changing the Status bar with fade in effect
function update_status_bar(message,color)
{
	$("#status_bar").hide()
	.html("<strong>" + message + "</strong>")
	.css("color",color)
	.fadeIn(1000);
}


// Function for Authentication 
// Part 1 - Display Login Prompt
function load_login_prompt()
{
	$("#content").hide().html('<div id="login_box" ></div>');
	$("#login_box")
		.append('<label>Username: </label><input id="username_field" name="username_field" /><br /><br />')
		.append('<label>LDAP Pass:&nbsp; </label><input type="password" id="password_field" name="password_field" /><br /><br />')
		.append('<button class="google-button" id="submit_login" onClick="process_login()">Login</button>');
	$("#content").fadeIn(1000);
	
	$('#password_field').keypress(function(e) 
	{
        if(e.which == 13) 
        {
            process_login();
        }
    });
}

// Function for Authentication
// Part 2 - Process login info
function process_login()
{
	update_status_bar("logging in....","green");
	
	var username_value = $("#username_field").val();
	var password_value = $("#password_field").val();
	

	$.ajax({
		type: "post",
		cache: false,
		async: false,
		url: "/auth/auth.php",
		data: { method: "authenticate_ldap", username : username_value, password : password_value},
		success: function(response) 
		{
			if(response == "OK")
			{
				update_status_bar("Authentication Successful","green");
				cookie_write(username_value);
				load_home();
			}
			else
			{
				update_status_bar("Authentication Failed","red");
			}
		}
	});	
}


function load_home()
{
	var access_string = "";
	var tools
	
	$.ajax({
		type: "post",
		cache: false,
		async: false,
		url: "/auth/auth.php",
		data: { method: "get_tool_access"},
		success: function(response) 
		{
			if(response == "none")
			{
				$("#content").hide()
				.html("<h1>You don't have access to this tool</h1><h2>Please contact <a href='mailto:email@email.com'>email@email.com</a> to get access </h2>")
				.fadeIn(1000);
			}
			else
			{
				$("#content").hide().html("<h1>Please Choose from the following</h1>").fadeIn(1000);
				var tool_access = response.split(",");
				for (var i=0 ; i < tool_access.length ; i++)
				{
					$("#content").append("<a href='#' onClick=launch_tool('" + tool_access[i] + "')><img src='images/" + tool_access[i] + ".png' style='padding:10px' style='border-style: none' style='margin:10px'></a>"); 
				}
			}
		}
	});	
}




/*
 * Creating an iframe to the destination
 */
function launch_tool(tools_name)
{
	switch(tools_name)
	{
		case "autoscale" : $("#content").hide().html("<iframe src='https://toolz.com/test3.php' width=100% height=100%></iframe>").fadeIn(1000); break;
		case "tableau_acrobat" : $("#content").hide().html("<iframe src='/tableau/cloud_ops_acrobat.html' width=100% height=100%></iframe>").fadeIn(1000); break;*/
	}
	
}


// If Check Successful, Response would be username
function cookie_check()
{
	update_status_bar("Checking Cookie..","blue");
	
	var result = null;
	
	$.ajax({
		type: "post",
		cache: false,
		async: false,
		url: "/auth/auth.php", 
		data: { method: "cookie_check_validity"},
		success: function(response) 
		{
			if(response != "expired" && response != "no_cookie")
			{
				//Update the status bar
				var welcome_message = "Welcome," + response;
				update_status_bar(welcome_message,"blue");
				result = "good_cookie";
			}
			else
			{
				//Update the status bar
				update_status_bar("Bad Cookie. Please Login...","red");
				result = "bad_cookie";
			}
		}
	});
	
	return result;
}

function cookie_write(username){
	//Update the status bar
	update_status_bar("About to Write Cookie...","green");
	
	$.ajax({
		type: "post",
		cache: false,
		async: false,
		url: "/auth/auth.php",
		data: { method: "cookie_write", username : username},
		success: function(response) 
		{
			if(response == "done")
			{
				//Update the status bar
				//update_status_bar("cookie baked","green")
				var welcome_message = "Welcome," + username;
				update_status_bar(welcome_message,"blue");
				return "cookie_baked";
			}
			else
			{
				//Update the status bar
				//update_status_bar(response,"red")
				return "failed_to_bake_cookie";
			}
		}
	});
}



