<?php
if(!defined('_TAI')) {
    die('Truy cập không hợp lệ');
}

$filter = filterData('get');

if(!empty($filter)){
    $course_id = $filter['id'];
    $checkCourse = getOne("SELECT * FROM course WHERE id = $course_id");
    if(!empty($checkCourse)){
       $deleteStatus = delete('course', "id = $course_id");
       if($deleteStatus){
        setSessionFlash('msg', 'Xóa khóa học thành công');
        setSessionFlash('msg_type', 'success');
        redirect("?module=course&action=list");
       }
    }else{
        setSessionFlash('msg', 'Khóa học không tồn tại');
        setSessionFlash('msg_type', 'danger');
    }
}else {
    setSessionFlash('msg', 'Đã có lỗi xảy ra vui lòng thử lại sau!!!');
        setSessionFlash('msg_type', 'danger');
}