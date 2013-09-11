Namaste = {};

function namasteConfirmDelete(frm, msg) {
	if(!confirm(msg)) return false;
	frm.del.value=1;
	frm.submit(); 
}

function namasteEnrollCourse(boxTitle, courseID, studentID, url) {
	tb_show(boxTitle, 
		url + '&course_id=' + courseID + 
		'&student_id=' + studentID);
}