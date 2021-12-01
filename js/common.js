Namaste = {};

function namasteConfirmDelete(frm, msg) {
	if(!confirm(msg)) return false;
	frm.del.value=1;
	frm.submit(); 
}

function namasteEnrollCourse(boxTitle, courseID, studentID, url, isSchool) {
   isSchool = isSchool || 0;
	tb_show(boxTitle, 
		url + '&course_id=' + courseID + 
		'&student_id=' + studentID +
		'&is_school=' + isSchool);
}

function namasteResetPoints(uid) {
	if(confirm("Are you sure?")) {
		var s = "?";
		var loc = new String(window.location);
		if(loc.indexOf('?') >= 0) s = "&";		
		window.location.href = loc + s + "namaste_cleanup_points=" + uid;  
	}
}

// loads the module selector in admin lessons page
function namasteLoadModules(courseID) {	
	data = {'action': 'namaste_ajax', 'type' : 'load_modules', 'course_id' : courseID, 'json' : 1};
	jQuery.post(namaste_i18n.ajax_url, data, function(msg) {
		var modules = jQuery.parseJSON(msg);
		html = '<select name="namaste_module_id" id="namaste_module_id">';
		html += '<option value="">'+namaste_i18n.all_modules+'</option>';
		jQuery(modules).each(function(i, module){
			html += '<option value="' + module.ID + '">' + module.post_title + '</option>';
		});
		html += '</select>';  
		jQuery('#namasteModuleSelector').html(html);
	});
}

NamastePay = {}
NamastePay.payWithMoolaMojo = function(id, url, isBundle, redirectURL) {
	isBundle = isBundle || 0; 
	redirectURL = redirectURL || '';
	
	data = {"id" : id, "is_bundle" : isBundle};
	jQuery.post(url, data, function(msg){
		if(msg == 'SUCCESS') {			
			if(redirectURL) window.location = redirectURL;
			else {
				window.location = window.location + "?paid=1";
				window.location.reload(); // because of FireFox
			}
		}
		else alert(msg);
	});
}