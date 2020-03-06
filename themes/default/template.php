<?php

//get the browser version
	$user_agent = http_user_agent();
	$browser_version =  $user_agent['version'];
	$browser_name =  $user_agent['name'];
	$browser_version_array = explode('.', $browser_version);

//set the doctype
	echo ($browser_name != "Internet Explorer") ? "<!DOCTYPE html>\n" : "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";

//get the php self path and set a variable with only the directory path
	$php_self_array = explode ("/", $_SERVER['PHP_SELF']);
	$php_self_dir = '';
	foreach ($php_self_array as &$value) {
		if (substr($value, -4) != ".php") {
			$php_self_dir .= $value."/";
		}
	}
	unset($php_self_array);
	if (strlen(PROJECT_PATH) > 0) {
		$php_self_dir = substr($php_self_dir, strlen(PROJECT_PATH), strlen($php_self_dir));
	}

echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>\n";
echo "<head>\n";
echo "<meta charset='utf-8'>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
echo "<meta name='viewport' content='width=device-width, initial-scale=1'>\n";

echo "<link rel='stylesheet' type='text/css' href='<!--{project_path}-->/resources/bootstrap/css/bootstrap.min.css'>\n";
echo "<link rel='stylesheet' type='text/css' href='<!--{project_path}-->/resources/bootstrap/css/bootstrap-tempusdominus.css'>\n";
echo "<link rel='stylesheet' type='text/css' href='<!--{project_path}-->/resources/bootstrap/css/bootstrap-colorpicker.min.css'>\n";
echo "<link rel='stylesheet' type='text/css' href='<!--{project_path}-->/themes/".escape($_SESSION['domain']['template']['name'])."/css.php".($default_login ? '?login=default' : null)."'>\n";
echo "<link rel='stylesheet' type='text/css' href='<!--{project_path}-->/resources/fontawesome/css/all.css'>\n";

//link to custom css file
	if ($_SESSION['theme']['custom_css']['text'] != '') {
		echo "<link rel='stylesheet' type='text/css' href='".$_SESSION['theme']['custom_css']['text']."'>\n\n";
	}
//output custom css
	if ($_SESSION['theme']['custom_css_code']['text'] != '') {
		echo "<style>\n";
		echo $_SESSION['theme']['custom_css_code']['text'];
		echo "</style>\n\n";
	}

//set fav icon
	$favicon = (isset($_SESSION['theme']['favicon']['text'])) ? $_SESSION['theme']['favicon']['text'] : '<!--{project_path}-->/themes/default/favicon.ico';
	echo "<link rel='icon' href='".$favicon."'>\n";

echo "<title><!--{title}--></title>\n";

echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/jquery/jquery-3.4.1.min.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/jquery/jquery.autosize.input.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/momentjs/moment-with-locales.min.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/bootstrap/js/bootstrap.min.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/bootstrap/js/bootstrap-tempusdominus.min.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/bootstrap/js/bootstrap-colorpicker.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/bootstrap/js/bootstrap-pwstrength.min.js'></script>\n";

echo "<script language='JavaScript' type='text/javascript'>window.FontAwesomeConfig = { autoReplaceSvg: false }</script>\n";
echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/fontawesome/js/all.js' defer></script>\n";

//web font loader
	if ($_SESSION['theme']['font_loader']['text'] == 'true') {
		if ($_SESSION['theme']['font_retrieval']['text'] != 'asynchronous') {
			$font_loader_version = ($_SESSION['theme']['font_loader_version']['text'] != '') ? escape($_SESSION['theme']['font_loader_version']['text']) : 1;
			echo "<script language='JavaScript' type='text/javascript' src='//ajax.googleapis.com/ajax/libs/webfont/".escape($font_loader_version)."/webfont.js'></script>\n";
		}
		echo "<script language='JavaScript' type='text/javascript' src='<!--{project_path}-->/resources/fonts/web_font_loader.php?v=".escape($font_loader_version)."'></script>\n";
	}
?>

<script language="JavaScript" type="text/javascript">

	//display message bar via js
		function display_message(msg, mood, delay) {
			mood = (typeof mood !== 'undefined') ? mood : 'default';
			delay = (typeof delay !== 'undefined') ? delay : <?php echo (1000 * (float) $_SESSION['theme']['message_delay']['text']); ?>;
			if (msg !== '') {
				var message_text = $(document.createElement('div'));
				message_text.addClass('message_text message_mood_'+mood);
				message_text.html(msg);
				message_text.on('click', function() {
					var object = $(this);
					object.clearQueue().finish();
					$("#message_container div").remove();
					$("#message_container").css({opacity: 0, 'height': 0}).css({'height': 'auto'});
				} );
				$("#message_container").append(message_text);
				message_text.css({'height': 'auto'}).animate({opacity: 1}, 250, function(){
					$("#message_container").delay(delay).animate({opacity: 0, 'height': 0}, 500, function() {
						$("#message_container div").remove();
						$("#message_container").animate({opacity: 1}, 300).css({'height': 'auto'});
					});
				});
			}
		}

	<?php if ($_SESSION['theme']['menu_style']['text'] == 'side') { ?>
		//toggle side menu visibility (if enabled)
			var menu_side_state = 'contracted';
			function menu_side_contract() {
				$('.menu_side_sub').slideUp(180);
				$('.menu_side_item_title').hide();
				<?php if ($_SESSION['theme']['menu_brand_type']['text'] == 'image' || $_SESSION['theme']['menu_brand_type']['text'] == '') { ?>
					$('#menu_brand_image_expanded').fadeOut(180, function() {
						$('#menu_brand_image_contracted').fadeIn(180);
					});
				<?php } else if ($_SESSION['theme']['menu_brand_type']['text'] == 'image_text') { ?>
					$('.menu_brand_text').hide();
					$('#menu_brand_image_contracted').animate({ width: '20px', 'margin-left': '-2px' }, 250);
				<?php } else if ($_SESSION['theme']['menu_brand_type']['text'] == 'text') { ?>
					$('.menu_brand_text').fadeOut(180);
				<?php } ?>
				$('#menu_side_container').animate({ width: '<?php echo is_numeric($_SESSION['theme']['menu_side_width_contracted']['text']) ? $_SESSION['theme']['menu_side_width_contracted']['text'] : '60'; ?>px' }, 250);
				$('#content_container').animate({ width: $(window).width() - <?php echo is_numeric($_SESSION['theme']['menu_side_width_contracted']['text']) ? $_SESSION['theme']['menu_side_width_contracted']['text'] : '60'; ?> }, 250, function() {
					menu_side_state = 'contracted';
				});

				$('.menu_side_contract').hide();
				$('.menu_side_expand').show();
			}

			function menu_side_expand() {
				<?php if ($_SESSION['theme']['menu_brand_type']['text'] == 'image_text') { ?>
					$('#menu_brand_image_contracted').animate({ width: '30px', 'margin-left': '0' }, 250);
				<?php } else if ($_SESSION['theme']['menu_brand_type']['text'] == 'image' || $_SESSION['theme']['menu_brand_type']['text'] == '') { ?>
					$('#menu_brand_image_contracted').fadeOut(180);
				<?php } ?>
				$('#menu_side_container').animate({ width: '<?php echo  is_numeric($_SESSION['theme']['menu_side_width_expanded']['text']) ? $_SESSION['theme']['menu_side_width_expanded']['text'] : '225'; ?>px' }, 250);
				$('#content_container').animate({ width: $(window).width() - <?php echo is_numeric($_SESSION['theme']['menu_side_width_expanded']['text']) ? $_SESSION['theme']['menu_side_width_expanded']['text'] : '225'; ?> }, 250, function() {
					$('.menu_brand_text').fadeIn(180);
					$('.menu_side_item_title').fadeIn(180);
					<?php if ($_SESSION['theme']['menu_brand_type']['text'] != 'none') { ?>
						$('.menu_side_contract').fadeIn(180);
					<?php } ?>
					<?php if ($_SESSION['theme']['menu_brand_type']['text'] == 'image' || $_SESSION['theme']['menu_brand_type']['text'] == '') { ?>
						$('#menu_brand_image_expanded').fadeIn(180);
					<?php } ?>
					menu_side_state = 'expanded';
				});
				<?php if ($_SESSION['theme']['menu_brand_type']['text'] == 'none') { ?>
					$('.menu_side_contract').show();
				<?php } ?>
				$('.menu_side_expand').hide();
			}
	<?php } ?>


	$(document).ready(function() {

		<?php echo message::html(true, "		"); ?>

		//hide message bar on hover
			$("#message_container").on('mouseenter',function() {
				$("#message_container div").remove();
				$("#message_container").css({opacity: 0, 'height': 0}).css({'height': 'auto'});
			});

		<?php
		if (permission_exists("domain_select") && count($_SESSION['domains']) > 1) {
			?>

			//domain selector controls
				$(".domain_selector_domain").on('click', function() { show_domains(); });
				$("#header_domain_selector_domain").on('click', function() { show_domains(); });
				$("#domains_hide").on('click', function() { hide_domains(); });

				function show_domains() {
					$('#domains_visible').val(1);
					var scrollbar_width = (window.innerWidth - $(window).width()); //gold: only solution that worked with body { overflow:auto } (add -ms-overflow-style: scrollbar; to <body> style for ie 10+)
					if (scrollbar_width > 0) {
						$("body").css({'margin-right':scrollbar_width, 'overflow':'hidden'}); //disable body scroll bars
						$(".navbar").css('margin-right',scrollbar_width); //adjust navbar margin to compensate
						$("#domains_container").css('right',-scrollbar_width); //domain container right position to compensate
					}
					$(document).scrollTop(0);
					$("#domains_container").show();
					$("#domains_block").animate({marginRight: '+=300'}, 400, function() {
						$("#domain_filter").trigger('focus');
					});
				}

				function hide_domains() {
					$('#domains_visible').val(0);
					$(document).ready(function() {
						$("#domains_block").animate({marginRight: '-=300'}, 400, function() {
							$("#domain_filter").val('');
							domain_search($("#domain_filter").val());
							$(".navbar").css('margin-right','0'); //restore navbar margin
							$("#domains_container").css('right','0'); //domain container right position
							$("#domains_container").hide();
							$("body").css({'margin-right':'0','overflow':'auto'}); //enable body scroll bars
							document.activeElement.blur();
						});
					});
				}

			<?php
		}

		//keyboard shortcut scripts

		//key: [enter] - retain default behavior to submit form, when present
			echo "	var action_bar_actions, first_form, first_submit;\n";
			echo "	action_bar_actions = document.querySelector('div#action_bar.action_bar > div.actions');\n";
			echo "	first_form = document.querySelector('form#frm');\n";

			echo "	if (action_bar_actions !== null) {\n";
			echo "		if (first_form !== null) {\n";
			echo "			first_submit = document.createElement('input');\n";
			echo "			first_submit.type = 'submit';\n";
			echo "			first_submit.id = 'default_submit';\n";
			echo "			first_submit.setAttribute('style',\"position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;\");\n"; //note: safari doesn't honor first submit element using "display: none;"
			echo "			first_form.prepend(first_submit);\n";
			echo "			window.addEventListener('keydown',function(e){\n";
			echo "				if (e.which == 13 && (e.target.tagName == 'INPUT' || e.target.tagName == 'SELECT')) {\n";
			echo "					if (typeof window.submit_form === 'function') { submit_form(); }\n";
			echo "					else { document.getElementById('frm').submit(); }\n";
			echo "				}\n";
			echo "			});\n";
			echo "		}\n";
			echo "	}\n";

		//common (used by delete and toggle)
			echo "	var list_checkboxes;\n";
			echo "	list_checkboxes = document.querySelectorAll('table.list tr.list-row td.checkbox input[type=checkbox]');\n";

		//keyup event listener
			echo "	window.addEventListener('keyup', function(e) {\n";

		//key: [escape] - close modal window, if open, or toggle domain selector
			echo "		if (e.which == 27) {\n";
			echo "			e.preventDefault();\n";
			echo "			var modals, modal_visible, modal;\n";
			echo "			modal_visible = false;\n";
			echo "			modals = document.querySelectorAll('div.modal-window');\n";
			echo "			if (modals.length !== 0) {\n";
			echo "				for (var x = 0, max = modals.length; x < max; x++) {\n";
			echo "					modal = document.getElementById(modals[x].id);\n";
			echo "					if (window.getComputedStyle(modal).getPropertyValue('opacity') == 1) {\n";
			echo "						modal_visible = true;\n";
			echo "					}\n";
			echo "				}\n";
			echo "			}\n";
			echo "			if (modal_visible) {\n";
			echo "				modal_close();\n";
			echo "			}\n";
			if (permission_exists("domain_select") && count($_SESSION['domains']) > 1) {
				echo "			else {\n";
				echo "				if (document.getElementById('domains_visible').value == 0) {\n";
				echo "					show_domains();\n";
				echo "				}\n";
				echo "				else { \n";
				echo "					hide_domains();\n";
				echo "				}\n";
				echo "			}\n";
			}
			echo "		}\n";

		//key: [insert], list: to add
			echo "		if (e.which == 45 && !(e.target.tagName == 'INPUT' && e.target.type == 'text') && e.target.tagName != 'TEXTAREA') {\n";
			echo "			e.preventDefault();\n";
			echo "			var list_add_button;\n";
			echo "			list_add_button = document.getElementById('btn_add');\n";
			echo "			if (list_add_button === null || list_add_button === 'undefined') {\n";
			echo "				list_add_button = document.querySelector('button[name=btn_add]');\n";
			echo "			}\n";
			echo "			if (list_add_button !== null) { list_add_button.click(); }\n";
			echo "		}\n";

		//key: [delete], list: to delete checked, edit: to delete
			echo "		if (e.which == 46 && !(e.target.tagName == 'INPUT' && e.target.type == 'text') && e.target.tagName != 'TEXTAREA') {\n";
			echo "			e.preventDefault();\n";
			echo "			if (list_checkboxes.length !== 0) {\n";
			echo "				var list_delete_button;\n";
			echo "				list_delete_button = document.querySelector('button[name=btn_delete]');\n";
			echo "				if (list_delete_button === null || list_delete_button === 'undefined') {\n";
			echo "					list_delete_button = document.getElementById('btn_delete');\n";
			echo "				}\n";
			echo "				if (list_delete_button !== null) { list_delete_button.click(); }\n";
			echo "			}\n";
			echo "			else {\n";
			echo "				var edit_delete_button;\n";
			echo "				edit_delete_button = document.querySelector('button[name=btn_delete]');\n";
			echo "				if (edit_delete_button === null || edit_delete_button === 'undefined') {\n";
			echo "					edit_delete_button = document.getElementById('btn_delete');\n";
			echo "				}\n";
			echo "				if (edit_delete_button !== null) { edit_delete_button.click(); }\n";
			echo "			}\n";
			echo "		}\n";

		//end keyup
			echo "	});\n";

		//keydown event listener
			echo "	window.addEventListener('keydown', function(e) {\n";

		//key: [space], list: to toggle checked
			echo "		if (e.which == 32 && !(e.target.tagName == 'INPUT' && e.target.type == 'text') && e.target.tagName != 'TEXTAREA' && list_checkboxes.length !== 0) {\n"; //note: for default [space] checkbox behavior (ie. toggle focused checkbox) include: " && !(e.target.tagName == 'INPUT' && e.target.type == 'checkbox')"
			echo "			e.preventDefault();\n";
			echo "			var list_toggle_button;\n";
			echo "			list_toggle_button = document.querySelector('button[name=btn_toggle]');\n";
			echo "			if (list_toggle_button === null || list_toggle_button === 'undefined') {\n";
			echo "				list_toggle_button = document.getElementById('btn_toggle');\n";
			echo "			}\n";
			echo "			if (list_toggle_button !== null) { list_toggle_button.click(); }\n";
			echo "		}\n";

		//key: [ctrl]+[a], list,edit: to check all
			echo "		if ((((e.which == 97 || e.which == 65) && (e.ctrlKey || e.metaKey)) || e.which == 19) && !(e.target.tagName == 'INPUT' && e.target.type == 'text') && e.target.tagName != 'TEXTAREA') {\n";
			echo "			var list_checkbox_all;\n";
			echo "			list_checkbox_all = document.querySelectorAll('table.list tr.list-header th.checkbox input[name=checkbox_all]');\n";
			echo "			if (list_checkbox_all !== null && list_checkbox_all.length > 0) {\n";
			echo "				e.preventDefault();\n";
			echo "				for (var x = 0, max = list_checkbox_all.length; x < max; x++) {\n";
			echo "					list_checkbox_all[x].click();\n";
			echo "				}\n";
			echo "			}\n";
			echo "			var edit_checkbox_all;\n";
			echo "			edit_checkbox_all = document.querySelectorAll('td.edit_delete_checkbox_all > span > input[name=checkbox_all]');\n";
			echo "			if (edit_checkbox_all !== null && edit_checkbox_all.length > 0) {\n";
			echo "				e.preventDefault();\n";
			echo "				for (var x = 0, max = edit_checkbox_all.length; x < max; x++) {\n";
			echo "					edit_checkbox_all[x].click();\n";
			echo "				}\n";
			echo "			}\n";
			echo "		}\n";

		//key: [ctrl]+[s], edit: to save
			echo "		if (((e.which == 115 || e.which == 83) && (e.ctrlKey || e.metaKey)) || (e.which == 19)) {\n";
			echo "			e.preventDefault();\n";
			echo "			var edit_save_button;\n";
			echo "			edit_save_button = document.getElementById('btn_save');\n";
			echo "			if (edit_save_button === null || edit_save_button === 'undefined') {\n";
			echo "				edit_save_button = document.querySelector('button[name=btn_save]');\n";
			echo "			}\n";
			echo "			if (edit_save_button !== null) { edit_save_button.click(); }\n";
			echo "		}\n";

		//end keydown
			echo "	});\n";

		?>

		//link table rows (except the last - the list_control_icons cell) on a table with a class of 'tr_hover', according to the href attribute of the <tr> tag
			$('.tr_hover tr,.list tr').each(function(i,e) {
				$(e).children('td:not(.list_control_icon,.list_control_icons,.tr_link_void,.list-row > .no-link,.list-row > .checkbox,.list-row > .button,.list-row > .action-button)').on('click', function() {
					var href = $(this).closest("tr").attr("href");
					var target = $(this).closest('tr').attr('target');
					if (href) {
						if (target) { window.open(href, target); }
						else { window.location = href; }
					}
				});
			});

		//autosize jquery autosize plugin on applicable input fields
			$("input[type=text].txt.auto-size,input[type=number].txt.auto-size,input[type=password].txt.auto-size,input[type=text].formfld.auto-size,input[type=number].formfld.auto-size,input[type=password].formfld.auto-size").autosizeInput();

		//initialize bootstrap tempusdominus (calendar/datetime picker) plugin
			$(function() {
				//set defaults
					$.fn.datetimepicker.Constructor.Default = $.extend({}, $.fn.datetimepicker.Constructor.Default, {
						buttons: {
							showToday: true,
							showClear: true,
							showClose: true,
						},
						icons: {
							time: 'fas fa-clock',
							date: 'fas fa-calendar-alt',
							up: 'fas fa-arrow-up',
							down: 'fas fa-arrow-down',
							previous: 'fas fa-chevron-left',
							next: 'fas fa-chevron-right',
							today: 'fas fa-calendar-check',
							clear: 'fas fa-trash',
							close: 'fas fa-times',
						}
					});

				//define formatting of individual classes
					$('.datepicker').datetimepicker({ 			format: 'YYYY-MM-DD', });
					$('.datetimepicker').datetimepicker({ 		format: 'YYYY-MM-DD HH:mm', });
					$('.datetimesecpicker').datetimepicker({ 	format: 'YYYY-MM-DD HH:mm:ss', });
			});

		//apply bootstrap colorpicker plugin
			$(function(){
				$('.colorpicker').colorpicker({
					align: 'left',
					customClass: 'colorpicker-2x',
					sliders: {
						saturation: {
							maxLeft: 200,
							maxTop: 200
						},
						hue: {
							maxTop: 200
						},
						alpha: {
							maxTop: 200
						}
					}
				});
			});

		//apply bootstrap password strength plugin
			$('#password').pwstrength({
				common: {
					minChar: 8,
					usernameField: '#username',
				},
				/* rules: { },  */
				ui: {
					//				very weak weak		normal	   medium	  strong	 very strong
					colorClasses: ["danger", "warning", "warning", "warning", "success", "success"],
					progressBarMinPercentage: 15,
					showVerdicts: false,
					viewports: {
						progress: "#pwstrength_progress"
					}
				}
			});

		<?php if ($_SESSION['theme']['menu_brand_image']['text'] != '' && $_SESSION['theme']['menu_brand_image_hover']['text'] != '' && $_SESSION['theme']['menu_style']['text'] != 'side') { ?>
			//crossfade menu brand images (if hover version set)
				$(function(){
					$('#menu_brand_image').on('mouseover',function(){
						$(this).fadeOut('fast', function(){
							$('#menu_brand_image_hover').fadeIn('fast');
						});
					});
					$('#menu_brand_image_hover').on('mouseout',function(){
						$(this).fadeOut('fast', function(){
							$('#menu_brand_image').fadeIn('fast');
						});
					});
				});
		<?php } ?>

		//generate resizeEnd event after window resize event finishes (used when side menu and on messages app)
			$(window).on('resize', function() {
				if (this.resizeTO) { clearTimeout(this.resizeTO); }
				this.resizeTO = setTimeout(function() { $(this).trigger('resizeEnd'); }, 180);
			});

		<?php if ($_SESSION['theme']['menu_style']['text'] == 'side') { ?>
			//side menu: adjust content container width after window resize
				$(window).on('resizeEnd', function() {
					$('#content_container').animate({ width: $(window).width() - $('#menu_side_container').width() }, 200);
				});
		<?php } ?>

	});

	//audio playback functions
		var recording_audio;
		var audio_clock;

		function recording_play(recording_id) {
			if (document.getElementById('recording_progress_bar_'+recording_id)) {
				document.getElementById('recording_progress_bar_'+recording_id).style.display='';
			}
			recording_audio = document.getElementById('recording_audio_'+recording_id);

			if (recording_audio.paused) {
				recording_audio.volume = 1;
				recording_audio.play();
				document.getElementById('recording_button_'+recording_id).innerHTML = "<span class='<?php echo $_SESSION['theme']['button_icon_pause']['text']; ?> fa-fw'></span>";
				audio_clock = setInterval(function () { update_progress(recording_id); }, 20);

				$("[id*=recording_button]").not("[id*=recording_button_"+recording_id+"]").html("<span class='<?php echo $_SESSION['theme']['button_icon_play']['text']; ?> fa-fw'></span>");
				$("[id*=recording_progress_bar]").not("[id*=recording_progress_bar_"+recording_id+"]").css('display', 'none');

				$('audio').each(function(){$('#menu_side_container').width()
					if ($(this).get(0) != recording_audio) {
						$(this).get(0).pause(); // Stop playing
						$(this).get(0).currentTime = 0; // Reset time
					}
				});
			}
			else {
				recording_audio.pause();
				document.getElementById('recording_button_'+recording_id).innerHTML = "<span class='<?php echo $_SESSION['theme']['button_icon_play']['text']; ?> fa-fw'></span>";
				clearInterval(audio_clock);
			}
		}

		function recording_stop(recording_id) {
			recording_reset(recording_id);
			clearInterval(audio_clock);
		}

		function recording_reset(recording_id) {
			recording_audio = document.getElementById('recording_audio_'+recording_id);
			recording_audio.pause();
			recording_audio.currentTime = 0;
			if (document.getElementById('recording_progress_bar_'+recording_id)) {
				document.getElementById('recording_progress_bar_'+recording_id).style.display='none';
			}
			document.getElementById('recording_button_'+recording_id).innerHTML = "<span class='<?php echo $_SESSION['theme']['button_icon_play']['text']; ?> fa-fw'></span>";
			clearInterval(audio_clock);
		}

		function update_progress(recording_id) {
			recording_audio = document.getElementById('recording_audio_'+recording_id);
			var recording_progress = document.getElementById('recording_progress_'+recording_id);
			var value = 0;
			if (recording_audio.currentTime > 0) {
				value = (100 / recording_audio.duration) * recording_audio.currentTime;
			}
			recording_progress.style.marginLeft = value + "%";
			if (parseInt(recording_audio.duration) > 30) { //seconds
				clearInterval(audio_clock);
			}
		}

	//handle action bar style on scroll
		window.addEventListener('scroll', function(){
			action_bar_scroll('action_bar', 20);
		}, false);
		function action_bar_scroll(action_bar_id, scroll_position, function_sticky, function_inline) {
			if (document.getElementById(action_bar_id)) {
				//sticky
					if (this.scrollY > scroll_position) {
						document.getElementById(action_bar_id).classList.add('scroll');
						if (typeof function_sticky === 'function') { function_sticky(); }
					}
				//inline
					if (this.scrollY < scroll_position) {
						document.getElementById(action_bar_id).classList.remove('scroll');
						if (typeof function_inline === 'function') { function_inline(); }
					}
			}
		}

	//enable button class button
		function button_enable(button_id) {
			button = document.getElementById(button_id);
			button.disabled = false;
			button.classList.remove('disabled');
			if (button.parentElement.nodeName == 'A') {
				anchor = button.parentElement;
				anchor.classList.remove('disabled');
				anchor.setAttribute('onclick','');
			}
		}

	//disable button class button
		function button_disable(button_id) {
			button = document.getElementById(button_id);
			button.disabled = true;
			button.classList.add('disabled');
			if (button.parentElement.nodeName == 'A') {
				anchor = button.parentElement;
				anchor.classList.add('disabled');
				anchor.setAttribute('onclick','return false;');
			}
		}

	//list functions
		function list_all_toggle(modifier) {
			var checkboxes = (modifier !== undefined) ? document.getElementsByClassName('checkbox_'+modifier) : document.querySelectorAll("input[type='checkbox']");
			var checkbox_checked = document.getElementById('checkbox_all' + (modifier !== undefined ? '_'+modifier : '')).checked;
			for (var i = 0, max = checkboxes.length; i < max; i++) {
				checkboxes[i].checked = checkbox_checked;
			}
			if (document.getElementById('btn_check_all') && document.getElementById('btn_check_none')) {
				if (checkbox_checked) {
					document.getElementById('btn_check_all').style.display = 'none';
					document.getElementById('btn_check_none').style.display = '';
				}
				else {
					document.getElementById('btn_check_all').style.display = '';
					document.getElementById('btn_check_none').style.display = 'none';
				}
			}
		}

		function list_all_check() {
			var inputs = document.getElementsByTagName('input');
			document.getElementById('checkbox_all').checked;
			for (var i = 0, max = inputs.length; i < max; i++) {
				if (inputs[i].type === 'checkbox') {
					inputs[i].checked = true;
				}
			}
		}

		function list_self_check(checkbox_id) {
			var inputs = document.getElementsByTagName('input');
			for (var i = 0, max = inputs.length; i < max; i++) {
				if (inputs[i].type === 'checkbox') {
					inputs[i].checked = false;
				}
			}
			document.getElementById(checkbox_id).checked = true;
		}

		function list_action_set(action) {
			document.getElementById('action').value = action;
		}

		function list_form_submit(form_id) {
			document.getElementById(form_id).submit();
		}

		function list_search_reset() {
			document.getElementById('btn_reset').style.display = 'none';
			document.getElementById('btn_search').style.display = '';
		}

		function edit_all_toggle(modifier) {
			var checkboxes = document.getElementsByClassName('checkbox_'+modifier);
			var checkbox_checked = document.getElementById('checkbox_all_'+modifier).checked;
			if (checkboxes.length > 0) {
				for (var i = 0; i < checkboxes.length; ++i) {
					checkboxes[i].checked = checkbox_checked;
				}
				if (document.getElementById('btn_delete')) {
					document.getElementById('btn_delete').value = checkbox_checked ? '' : 'delete';
				}
			}
		}

		function edit_delete_action(modifier) {
			var checkboxes = document.getElementsByClassName('chk_delete');
			if (document.getElementById('btn_delete') && checkboxes.length > 0) {
				var checkbox_checked = false;
				for (var i = 0; i < checkboxes.length; ++i) {
					if (checkboxes[i].checked) {
						checkbox_checked = true;
					}
					else {
						if (document.getElementById('checkbox_all'+(modifier !== undefined ? '_'+modifier : ''))) {
							document.getElementById('checkbox_all'+(modifier !== undefined ? '_'+modifier : '')).checked = false;
						}
					}
				}
				document.getElementById('btn_delete').value = checkbox_checked ? '' : 'delete';
			}
		}

		function swap_display(a_id, b_id, display_value) {
			display_value = display_value !== undefined ? display_value : 'inline-block';
			a = document.getElementById(a_id);
			b = document.getElementById(b_id);
			if (window.getComputedStyle(a).display === 'none') {
				a.style.display = display_value;
				b.style.display = 'none';
			}
			else {
				a.style.display = 'none';
				b.style.display = display_value;
			}
		}

		function modal_close() {
			document.location.href='#';
			document.activeElement.blur();
		}

		function hide_password_fields() {
			var password_fields = document.querySelectorAll("input[type='password']");
			for (var p = 0, max = password_fields.length; p < max; p++) {
				password_fields[p].style.visibility = 'hidden';
				password_fields[p].type = 'text';
			}
		}

		window.addEventListener('beforeunload', function(e){
			hide_password_fields();
		});

</script>

<?php

echo "<!--{head}-->\n";
echo "</head>\n";

//add multilingual support
	$language = new text;
	$text = $language->get(null,'themes/default');

echo "<body onload=\"".$onload."\">\n";

echo "	<div id='message_container'></div>\n";

//logged in, show the domains block
	if (strlen($_SESSION["username"]) > 0 && permission_exists("domain_select") && count($_SESSION['domains']) > 1) {

		echo "<div id='domains_container'>\n";
		echo "	<input type='hidden' id='domains_visible' value='0'>\n";
		echo "	<div id='domains_block'>\n";
		echo "		<div id='domains_header'>\n";
		echo "			<input id='domains_hide' type='button' class='btn' style='float: right' value=\"".$text['theme-button-close']."\">\n";

		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/app/domains/domains.php")) {
			$domain_path = PROJECT_PATH.'/app/domains/domains.php';
		}
		else {
			$domain_path = PROJECT_PATH.'/core/domains/domains.php';
		}
		echo "			<a href=\"".$domain_path."\"><b style=\"color: #000;\">".$text['theme-title-domains']."</b></a> (".sizeof($_SESSION['domains']).")";
		echo "			<br><br>\n";
		echo "			<input type='text' id='domain_filter' class='formfld' style='margin-left: 0; min-width: 100%; width: 100%;' placeholder=\"".$text['theme-label-search']."\" onkeyup='domain_search(this.value)'>\n";
		echo "		</div>\n";
		echo "		<div id='domains_list'>\n";

		//alternating background colors of inactive domains
		if ($_SESSION['theme']['domain_inactive_background_color'][0] != '') {
			$bgcolor1 = $_SESSION['theme']['domain_inactive_background_color'][0];
		}
		else {
			$bgcolor1 = "#eaedf2";
		}	
		if ($_SESSION['theme']['domain_inactive_background_color'][1] != '') {
			$bgcolor2 = $_SESSION['theme']['domain_inactive_background_color'][1];
		}	
		else {
			$bgcolor2 = "#fff";
		}
		
		foreach($_SESSION['domains'] as $domain) {
			//active domain color
			$bgcolor = ($bgcolor == $bgcolor1) ? $bgcolor2 : $bgcolor1;
			if ($_SESSION['theme']['domain_active_background_color']['text'] != '') {
				$bgcolor = ($domain['domain_uuid'] == $_SESSION['domain_uuid']) ? escape($_SESSION['theme']['domain_active_background_color']['text']) : $bgcolor;
			}
			else {
				$bgcolor = ($domain['domain_uuid'] == $_SESSION['domain_uuid']) ? "#eeffee" : $bgcolor;
			}
			//active domain's text hover color
			if ($_SESSION['theme']['domain_active_text_color_hover']['text'] != '' && $domain['domain_uuid'] == $_SESSION['domain_uuid']) {
				echo "<div id=\"".$domain['domain_name']."\" class='domains_list_item_active' style='background-color: ".$bgcolor."' onclick=\"document.location.href='".escape($domain_path)."?domain_uuid=".escape($domain['domain_uuid'])."&domain_change=true';\">";
			}
			else if ($_SESSION['theme']['domain_inactive_text_color_hover']['text'] != '' && $domain['domain_uuid'] != $_SESSION['domain_uuid']) {
				echo "<div id=\"".$domain['domain_name']."\" class='domains_list_item_inactive' style='background-color: ".$bgcolor."' onclick=\"document.location.href='".escape($domain_path)."?domain_uuid=".escape($domain['domain_uuid'])."&domain_change=true';\">";
			}
			else {
				echo "<div id=\"".$domain['domain_name']."\" class='domains_list_item' style='background-color: ".$bgcolor."' onclick=\"document.location.href='".escape($domain_path)."?domain_uuid=".escape($domain['domain_uuid'])."&domain_change=true';\">";
			}
			echo "<a href='".escape($domain_path)."?domain_uuid=".escape($domain['domain_uuid'])."&domain_change=true' ".(($domain['domain_uuid'] == $_SESSION['domain_uuid']) ? "style='font-weight: bold;'" : null).">".escape($domain['domain_name'])."</a>\n";
			if ($domain['domain_description'] != '') {
				//active domain description text color
				if ($_SESSION['theme']['domain_active_desc_text_color']['text'] != '' && $domain['domain_uuid'] == $_SESSION['domain_uuid']) {
					echo "<span class='domain_active_list_item_description' title=\"".escape($domain['domain_description'])."\"> - ".escape($domain['domain_description'])."</span>\n";
				}
				//inactive domains description text color
				else if ($_SESSION['theme']['domain_inactive_desc_text_color']['text'] != '' && $domain['domain_uuid'] != $_SESSION['domain_uuid']) {
					echo "<span class='domain_inactive_list_item_description' title=\"".escape($domain['domain_description'])."\"> - ".escape($domain['domain_description'])."</span>\n";
				}
				//default domain description text color
				else {
					echo "<span class='domain_list_item_description' title=\"".escape($domain['domain_description'])."\"> - ".escape($domain['domain_description'])."</span>\n";
				}
			}
			echo "</div>\n";
			$ary_domain_names[] = $domain['domain_name'];
			$ary_domain_descs[] = str_replace('"','\"',$domain['domain_description']);
		}

		echo "		</div>\n";

		echo "		<script>\n";
		echo "			var domain_names = new Array(\"".implode('","', $ary_domain_names)."\");\n";
		echo "			var domain_descs = new Array(\"".implode('","', $ary_domain_descs)."\");\n";
		echo "			function domain_search(criteria) {\n";
		echo "				for (var x = 0; x < domain_names.length; x++) {\n";
		echo "					if (domain_names[x].toLowerCase().match(criteria.toLowerCase()) || domain_descs[x].toLowerCase().match(criteria.toLowerCase())) {\n";
		echo "						document.getElementById(domain_names[x]).style.display = '';\n";
		echo "					}\n";
		echo "					else {\n";
		echo "						document.getElementById(domain_names[x]).style.display = 'none';\n";
		echo "					}\n";
		echo "				}\n";
		echo "			}\n";
		echo "		</script>\n";

		echo "	</div>\n";
		echo "</div>\n";

	}

// qr code container for contacts
	echo "<div id='qr_code_container' style='display: none;' onclick='$(this).fadeOut(400);'>\n";
	echo "	<table cellpadding='0' cellspacing='0' border='0' width='100%' height='100%'><tr><td align='center' valign='middle'>\n";
	echo "		<span id='qr_code' onclick=\"$('#qr_code_container').fadeOut(400);\"></span>\n";
	echo "	</td></tr></table>\n";
	echo "</div>\n";


if (!$default_login) {

	//top fixed, static or inline boostrap menu
	function show_menu($menu_array, $menu_style, $menu_position) {
		global $text;

		//determine menu behavior
			switch ($menu_style) {
				case 'inline':
					$menu_type = 'default';
					$menu_width = 'calc(100% - 20px)';
					$menu_brand = false;
					$menu_corners = null;
					break;
				case 'static':
					$menu_type = 'static-top';
					$menu_width = 'calc(100% - 40px)';
					$menu_brand = true;
					$menu_corners = "style='-webkit-border-radius: 0 0 4px 4px; -moz-border-radius: 0 0 4px 4px; border-radius: 0 0 4px 4px;'";
					break;
				case 'fixed':
				default:
					$menu_position = ($menu_position != '') ? $menu_position : 'top';
					$menu_type = 'fixed-'.$menu_position;
					if (!http_user_agent('mobile')) {
						$menu_width = $_SESSION['theme']['menu_width_fixed']['text'] != '' ? $_SESSION['theme']['menu_width_fixed']['text'] : 'calc(90% - 20px)';
					}
					$menu_brand = true;
					$menu_corners = null;
			}

		//begin navbar code
			echo "<nav class='navbar navbar-expand-sm ".$menu_type."' ".$menu_corners.">\n";
 			echo "	<div class='container-fluid' style='width: ".$menu_width."; padding: 0;'>\n";
			echo "		<div class='navbar-brand'>\n";

			if ($menu_brand) {
				//define menu brand link
					if (strlen(PROJECT_PATH) > 0) {
						$menu_brand_link = PROJECT_PATH;
					}
					else if (!$default_login) {
						$menu_brand_link = '/';
					}
				//define menu brand mark
					$menu_brand_text = ($_SESSION['theme']['menu_brand_text']['text'] != '') ? escape($_SESSION['theme']['menu_brand_text']['text']) : "FusionPBX";
					switch ($_SESSION['theme']['menu_brand_type']['text']) {
						case 'text':
							echo "			<a class='navbar-brand-text'  href=\"".$menu_brand_link."\">".$menu_brand_text."</a>\n";
							break;
						case 'image_text':
							$menu_brand_image = ($_SESSION['theme']['menu_brand_image']['text'] != '') ? escape($_SESSION['theme']['menu_brand_image']['text']) : PROJECT_PATH."/themes/default/images/logo.png";
							echo "			<a href='".$menu_brand_link."'>";
							echo "				<img id='menu_brand_image' class='navbar-logo' src='".$menu_brand_image."' title=\"".escape($menu_brand_text)."\">";
							if ($_SESSION['theme']['menu_brand_image_hover']['text'] != '') {
								echo 			"<img id='menu_brand_image_hover' class='navbar-logo' style='display: none;' src='".$_SESSION['theme']['menu_brand_image_hover']['text']."' title=\"".escape($menu_brand_text)."\">";
							}
							echo 			"</a>\n";
							echo "			<a class='navbar-brand-text' href=\"".$menu_brand_link."\">".$menu_brand_text."</a>\n";
							break;
						case 'none':
							break;
						case 'image':
						default:
							$menu_brand_image = ($_SESSION['theme']['menu_brand_image']['text'] != '') ? escape($_SESSION['theme']['menu_brand_image']['text']) : PROJECT_PATH."/themes/default/images/logo.png";
							echo "			<a href='".$menu_brand_link."'>";
							echo "				<img id='menu_brand_image' class='navbar-logo' src='".$menu_brand_image."' title=\"".escape($menu_brand_text)."\">";
							if (isset($_SESSION['theme']['menu_brand_image_hover']['text']) && $_SESSION['theme']['menu_brand_image_hover']['text'] != '') {
								echo 			"<img id='menu_brand_image_hover' class='navbar-logo' style='display: none;' src='".$_SESSION['theme']['menu_brand_image_hover']['text']."' title=\"".escape($menu_brand_text)."\">";
							}
							echo 			"</a>\n";
							echo "			<a style='margin: 0;'></a>\n";
					}
			}

			echo "		</div>\n";

			echo "		<button type='button' class='navbar-toggler' data-toggle='collapse' data-target='#main_navbar' aria-expanded='false' aria-controls='main_navbar' aria-label='Toggle Menu'>\n";
			echo "			<span class='fas fa-bars'></span>\n";
			echo "		</button>\n";

			echo "		<div class='collapse navbar-collapse' id='main_navbar'>\n";
			echo "			<ul class='navbar-nav'>\n";

			foreach ($menu_array as $index_main => $menu_parent) {
				$mod_li = "nav-item";
				$mod_a_1 = "";
				$submenu = false;
				if (is_array($menu_parent['menu_items']) && sizeof($menu_parent['menu_items']) > 0) {
					$mod_li = "nav-item dropdown ";
					$mod_a_1 = "data-toggle='dropdown' ";
					$submenu = true;
				}
				$mod_a_2 = ($menu_parent['menu_item_link'] != '' && !$submenu) ? $menu_parent['menu_item_link'] : '#';
				$mod_a_3 = ($menu_parent['menu_item_category'] == 'external') ? "target='_blank' " : null;
				if (isset($_SESSION['theme']['menu_main_icons']['boolean']) && $_SESSION['theme']['menu_main_icons']['boolean'] == 'true') {
					if ($menu_parent['menu_item_icon'] != '' && substr_count($menu_parent['menu_item_icon'], 'fa-') > 0) {
						$menu_main_icon = "<span class='fas ".$menu_parent['menu_item_icon']."' title=\"".escape($menu_parent['menu_language_title'])."\"></span>\n";
					}
					else {
						$menu_main_icon = null;
					}
					$menu_main_item = "<span class='d-sm-none d-md-none d-lg-inline' style='margin-left: 5px;'>".$menu_parent['menu_language_title']."</span>\n";
				}
				else {
					$menu_main_item = $menu_parent['menu_language_title'];
				}
				echo "				<li class='".$mod_li."'>\n";
				echo "					<a class='nav-link' ".$mod_a_1." href='".$mod_a_2."' ".$mod_a_3.">\n";
				echo "						".$menu_main_icon.$menu_main_item;
				echo "					</a>\n";
				if ($submenu) {
					echo "					<ul class='dropdown-menu'>\n";
					foreach ($menu_parent['menu_items'] as $index_sub => $menu_sub) {
						$mod_a_2 = $menu_sub['menu_item_link'];
						if ($mod_a_2 == '') {
							$mod_a_2 = '#';
						}
						$mod_a_3 = ($menu_sub['menu_item_category'] == 'external') ? "target='_blank' " : null;
						if ($_SESSION['theme']['menu_sub_icons']['boolean'] != 'false') {
							if ($menu_sub['menu_item_icon'] != '' && substr_count($menu_sub['menu_item_icon'], 'fa-') > 0) {
								$menu_sub_icon = "<span class='fas ".escape($menu_sub['menu_item_icon'])."'></span>";
							}
							else {
								$menu_sub_icon = null;
							}
						}
						echo "						<li class='nav-item'><a class='nav-link' href='".$mod_a_2."' ".$mod_a_3.">".($_SESSION['theme']['menu_sub_icons'] ? "<span class='fas fa-bar d-inline-block d-sm-none float-left' style='margin: 4px 10px 0 25px;'></span>" : null).escape($menu_sub['menu_language_title']).$menu_sub_icon."</a></li>\n";
					}
					echo "					</ul>\n";
				}
				echo "				</li>\n";
			}

			echo "			</ul>\n";

			echo "			<ul class='navbar-nav ml-auto'>\n";
			//domain name/selector
				if ($_SESSION["username"] != '' && permission_exists("domain_select") && count($_SESSION['domains']) > 1 && $_SESSION['theme']['domain_visible']['text'] == 'true') {
					echo "		<li class='nav-item'>\n";
					echo "			<a class='nav-link domain_selector_domain' href='#' title='".$text['theme-label-open_selector']."'>".escape($_SESSION['domain_name'])."</a>";
					echo "		</li>\n";
				}
			//logout icon
				if ($_SESSION['username'] != '' && $_SESSION['theme']['logout_icon_visible']['text'] == "true") {
					$username_full = $_SESSION['username'].((count($_SESSION['domains']) > 1) ? "@".$_SESSION["user_context"] : null);
					echo "		<li class='nav-item'>\n";
					echo "			<a class='nav-link logout_icon' href='".PROJECT_PATH."/logout.php' title=\"".$text['theme-label-logout']."\" onclick=\"return confirm('".$text['theme-confirm-logout']."')\"><span class='fas fa-sign-out-alt'></span></a>";
					echo "		</li>\n";
					unset($username_full);
				}
			echo "			</ul>\n";

			echo "		</div>\n";
			echo "	</div>\n";
			echo "</nav>\n";
	}

	//get the menu array and save it to the session
		if (!isset($_SESSION['menu']['array'])) {
			$menu = new menu;
			$menu->menu_uuid = $_SESSION['domain']['menu']['uuid'];
			$_SESSION['menu']['array'] = $menu->menu_array();
			unset($menu);
		}
	//get the menu style and position
		$menu_style = ($_SESSION['theme']['menu_style']['text'] != '') ? $_SESSION['theme']['menu_style']['text'] : 'fixed';
		$menu_position = ($_SESSION['theme']['menu_position']['text'] != '') ? $_SESSION['theme']['menu_position']['text'] : 'top';

	//show the menu style
		switch ($menu_style) {
			case 'inline':
				$logo_align = ($_SESSION['theme']['logo_align']['text'] != '') ? $_SESSION['theme']['logo_align']['text'] : 'left';
				$logo_style = ($_SESSION['theme']['logo_style']['text'] != '') ? $_SESSION['theme']['logo_style']['text'] : null;
				echo "<div class='container-fluid' style='padding: 0;' align='".$logo_align."'>\n";
				if ($_SERVER['PHP_SELF'] != PROJECT_PATH."/core/install/install.php") {
					$logo = ($_SESSION['theme']['logo']['text'] != '') ? $_SESSION['theme']['logo']['text'] : PROJECT_PATH."/themes/default/images/logo.png";
					echo "<a href='".((PROJECT_PATH != '') ? PROJECT_PATH : '/')."'><img src='".$logo."' style='padding: 15px 20px; ".$logo_style."'></a>";
				}

				show_menu($_SESSION['menu']['array'], $menu_style, $menu_position);
				break;
			case 'static':
				echo "<div class='container-fluid' style='padding: 0;' align='center'>\n";
				show_menu($_SESSION['menu']['array'], $menu_style, $menu_position);
				break;
			case 'fixed':
				show_menu($_SESSION['menu']['array'], $menu_style, $menu_position);
				echo "<div class='container-fluid' style='padding: 0;' align='center'>\n";
				break;
			case 'side':
				echo "<div id='menu_side_container'>\n";
				//menu brand image and/or text
					if ($_SESSION['theme']['menu_brand_type']['text'] == 'none') {
						echo "	<div style='height: 75px;'>\n";
						echo 		"<a class='menu_side_item_main menu_side_contract' onclick='menu_side_contract();' style='display: none;'><i class='fas fa-chevron-left' style='z-index: 99800; padding-left: 3px;'></i></a>";
						echo 		"<a class='menu_side_item_main menu_side_expand' onclick='menu_side_expand();'><i class='fas fa-bars' style='z-index: 99800; padding-left: 3px;'></i></a>";
						echo 	"</div>\n";
					}
					else {
						echo "	<div id='menu_side_brand_container'>\n";
						//menu toggle buttons
							if ($_SESSION['theme']['menu_brand_type']['text'] != 'none') {
								echo "		<div style='float: right; margin-right: -20px; margin-top: -20px;'>\n";
								echo "			<a class='menu_side_item_main menu_side_contract' onclick='menu_side_contract();' style='display: none;'><i class='fas fa-chevron-left'></i></a>\n";
								echo "		</div>\n";
							}
						//define the menu brand link
							if (strlen(PROJECT_PATH) > 0) {
								$menu_brand_link = PROJECT_PATH;
							}
							else if (!$default_login) {
								$menu_brand_link = '/';
							}
						//show the menu brand image and/or text
							$menu_brand_image_contracted =  $_SESSION['theme']['menu_side_brand_image_contracted']['text'] != '' ? $_SESSION['theme']['menu_side_brand_image_contracted']['text'] : PROJECT_PATH."/themes/default/images/logo_side_contracted.png";
							$menu_brand_image_expanded =  $_SESSION['theme']['menu_side_brand_image_expanded']['text'] != '' ? $_SESSION['theme']['menu_side_brand_image_expanded']['text'] : PROJECT_PATH."/themes/default/images/logo_side_expanded.png";
							$menu_brand_text = ($_SESSION['theme']['menu_brand_text']['text'] != '') ? escape($_SESSION['theme']['menu_brand_text']['text']) : "FusionPBX";
							if ($_SESSION['theme']['menu_brand_type']['text'] == 'image' || $_SESSION['theme']['menu_brand_type']['text'] == '') {
								echo "		<a href='".$menu_brand_link."' style='text-decoration: none;'>";
								echo 			"<img id='menu_brand_image_contracted' style='width: 20px; margin-left: -2px; margin-top: -5px;' src='".escape($menu_brand_image_contracted)."' title=\"".escape($menu_brand_text)."\">";
								echo 			"<img id='menu_brand_image_expanded' style='display: none;' src='".escape($menu_brand_image_expanded)."' title=\"".escape($menu_brand_text)."\">";
								echo 		"</a>\n";
							}
							else if ($_SESSION['theme']['menu_brand_type']['text'] == 'image_text') {
								echo "		<a href='".$menu_brand_link."' style='text-decoration: none;'>";
								echo 			"<img id='menu_brand_image_contracted' style='width: 20px; margin-left: -2px; margin-top: -5px;' src='".escape($menu_brand_image_contracted)."' title=\"".escape($menu_brand_text)."\">";
								echo 			"<span class='menu_brand_text' style='display: none;'>".$menu_brand_text."</span>";
								echo 		"</a>\n";
							}
							else if ($_SESSION['theme']['menu_brand_type']['text'] == 'text') {
								echo "		<a class='menu_brand_text' style='display: none;' href=\"".$menu_brand_link."\">".$menu_brand_text."</a>\n";
							}
						echo "	</div>\n";
					}

					//main menu items
						if (is_array($_SESSION['menu']['array']) && sizeof($_SESSION['menu']['array']) != 0) {
							foreach ($_SESSION['menu']['array'] as $menu_index_main => $menu_item_main) {
								echo "	<a class='menu_side_item_main' ".($menu_item_main['menu_item_link'] != '' ? "href='".$menu_item_main['menu_item_link']."'" : "onclick=\"menu_side_expand(); $('#sub_".$menu_item_main['menu_item_uuid']."').slideToggle(180, function() { if (!$(this).is(':hidden')) { $('.menu_side_sub').not($(this)).slideUp(180); } });\"")." title=\"".$menu_item_main['menu_language_title']."\">";
								if ($menu_item_main['menu_item_icon'] != '') {
									echo "<i class='fas ".$menu_item_main['menu_item_icon']." fa-fw' style='z-index: 99800; margin-right: 8px;'></i>";
								}
								echo "<span class='menu_side_item_title' style='display: none;'>".$menu_item_main['menu_language_title']."</span>";
								echo "</a>\n";
								//sub menu items
									if (is_array($menu_item_main['menu_items']) && sizeof($menu_item_main['menu_items']) != 0) {
										echo "	<div id='sub_".$menu_item_main['menu_item_uuid']."' class='menu_side_sub' style='display: none;'>\n";
										foreach ($menu_item_main['menu_items'] as $menu_index_sub => $menu_item_sub) {
											echo "		<a class='menu_side_item_sub' ".($menu_item_sub['menu_item_category'] == 'external' ? "target='_blank'" : null)." href='".$menu_item_sub['menu_item_link']."'>";
											echo 			"<span class='menu_side_item_title' style='display: none;'>".$menu_item_sub['menu_language_title']."</span>";
											echo 		"</a>\n";
										}
										echo "	</div>\n";
									}
							}
							echo "	<div style='height: 100px;'></div>\n";
						}
				echo "</div>\n";
				echo "<div id='content_container' style='padding: 0; width: calc(100% - ".(is_numeric($_SESSION['theme']['menu_side_width_contracted']['text']) ? $_SESSION['theme']['menu_side_width_contracted']['text'] : '55')."px); float: right; padding-top: 0px; text-align: center;'>\n";
				echo "	<div id='content_header'>\n";
				//header: left
					echo "<div class='float-left'>\n";
					echo "</div>\n";
				//header: right
					echo "<span class='float-right' style='white-space: nowrap;'>";
					//current user
						echo "<span style='display: inline-block; padding-right: 20px; font-size: 85%;'>\n";
						echo "<strong>".$text['theme-label-user']."</strong>: ";
						echo "<a href='".PROJECT_PATH."/core/users/user_edit.php?id=user'>".$_SESSION['username']."</a>";
						echo "</span>\n";
					//domain name/selector (sm+)
						if ($_SESSION["username"] != '' && permission_exists("domain_select") && count($_SESSION['domains']) > 1 && $_SESSION['theme']['domain_visible']['text'] == 'true') {
							echo "<span style='display: inline-block; padding-right: 10px; font-size: 85%;'>\n";
							echo "<strong>".$text['theme-label-domain']."</strong>: ";
							echo "<a href='#' id='header_domain_selector_domain' title='".$text['theme-label-open_selector']."'>".escape($_SESSION['domain_name'])."</a>";
							echo "</span>\n";
						}
					//logout icon
						if ($_SESSION['username'] != '' && $_SESSION['theme']['logout_icon_visible']['text'] == "true") {
							echo "<a id='header_logout_icon' href='".PROJECT_PATH."/logout.php' title=\"".$text['theme-label-logout']."\" onclick=\"return confirm('".$text['theme-confirm-logout']."')\"><span class='fas fa-log-out'></span></a>";
						}
					echo "</span>";
				echo "	</div>\n";
				break;
		}

		echo "<div id='main_content'>\n";
		echo "	<!--{body}-->\n";
		echo "</div>\n";
		echo "<div id='footer'>\n";
		echo "	<span class='footer'>".(isset($_SESSION['theme']['footer']['text']) && $_SESSION['theme']['footer']['text'] != '' ? $_SESSION['theme']['footer']['text'] : "&copy; ".$text['theme-label-copyright']." 2008 - ".date("Y")." <a href='http://www.fusionpbx.com' class='footer' target='_blank'>fusionpbx.com</a> ".$text['theme-label-all_rights_reserved'])."</span>\n";
		echo "</div>\n";

		echo "</div>\n"; //initial div from switch statement above
}
else {
	//default login being used
	if ($_SESSION['theme']['logo_login']['text'] != '') {
		$logo = $_SESSION['theme']['logo_login']['text'];
	}
	else if ($_SESSION['theme']['logo']['text'] != '') {
		$logo = $_SESSION['theme']['logo']['text'];
	}
	else {
		$logo = PROJECT_PATH."/themes/default/images/logo_login.png";
	}

	//set the login logo width and height
	if (isset($_SESSION['theme']['login_logo_width']['text'])) {
		$login_logo_width = $_SESSION['theme']['login_logo_width']['text'];
	}
	else {
		$login_logo_width = 'auto; max-width: 300px';
	}
	if (isset($_SESSION['theme']['login_logo_height']['text'])) {
		$login_logo_height = $_SESSION['theme']['login_logo_height']['text'];
	}
	else {
		$login_logo_height = 'auto; max-height: 300px';
	}

	echo "<div id='default_login'>\n";
	echo "	<a href='".PROJECT_PATH."/'><img id='login_logo' style='width: ".$login_logo_width."; height: ".$login_logo_height.";' src='".escape($logo)."'></a><br />\n";
	echo "	<!--{body}-->\n";
	echo "</div>\n";
	echo "<div id='footer_login'>\n";
	echo "	<span class='footer'>".($_SESSION['theme']['footer']['text'] != '' ? $_SESSION['theme']['footer']['text'] : "&copy; ".$text['theme-label-copyright']." 2008 - ".date("Y")." <a href='http://www.fusionpbx.com' class='footer' target='_blank'>fusionpbx.com</a> ".$text['theme-label-all_rights_reserved'])."</span>\n";
	echo "</div>\n";

	unset($_SESSION['background_image']);
}

echo "</body>\n";
echo "</html>\n";

?>
