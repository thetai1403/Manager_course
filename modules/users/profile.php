<?php
if(!defined('_TAI')) {
    die('Truy cập không hợp lệ');
}

$data = [
    'title' => 'Profile'
];
layout('header', $data);
layout('sidebar');
$getData = filterData('get');

//Lấy thông tin user

$token = getSession('token_login');
if(!empty($token)){
    $checkTokenLogin = getOne("SELECT *FROM token_login WHERE token = '$token'");
    if(!empty($checkTokenLogin)){
        $user_id = $checkTokenLogin['user_id'];
        $detailUser = getOne("SELECT  * FROM users WHERE id = $user_id");
    }
}

if(isPost()){
    $filter = filterData();
    $errors = [];

    // validate fullname
    if(empty(trim($filter['fullname']))){
        $errors['fullname']['required'] = 'Họ tên bắt buộc phải nhập';
    }else{
        if(strlen(trim(($filter['fullname'])))<5){
            $errors['fullname']['length'] = 'Họ tên phải hơn 5 kí tự';
        }
    }

    if($filter['email'] != $detailUser['email']){
         //Validate email 
        if(empty(trim($filter['email']))){
            $errors['email']['required'] = 'Email bắt buộc phải nhập';
        }else {
            //Đúng định dạng email, email tồn tại chưa
            if(!validateEmail(trim($filter['email']))){
            $errors['email']['isEmail'] = 'Email không đúng định dạng';
            }else{
                $email = $filter['email'];
    
                $checkEmail = getRows("SELECT * FROM users WHERE email = '$email'");
                if($checkEmail > 0){ 
                    $errors['email']['check'] = 'Email đã tồn tại';
                }
            }
        }
    }
   
    //Validate password
    if(empty(trim($filter['password']))){
        if (strlen(trim($filter['password']))<6){
            $errors['password']['length'] = 'Mật khẩu phải lớn hơn 6 kí tự';
        }
    }
    if(empty($errors)){

        $dataUpdate = [
            'fullname' => $filter['fullname'],
            'email' => $filter['email'],
            'phone' => $filter['phone'],
            'address' => (!empty($filter['address']) ? $filter['address'] : null),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if(!empty($_FILES['avatar']['name'])){
            //Xử lý avatar upload ảnh
        $uploadDir = 'templates/uploads/';
        if(!file_exists($uploadDir)){
            mkdir($uploadDir, 0777, true); //tạo thư mục nếu chưa tồn tại
        }
        
        $fileName = basename($_FILES['avatar']['name']);
        $targetFile = $uploadDir .time() . '-' . $fileName;

        $thumb = '';
        $checkMove = move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile);
        if($checkMove){
            $thumb = $targetFile;
        }
        $dataUpdate ['avatar'] = $thumb;
        }

        if(!empty($filter['password'])){
            $dataUpdate['password'] = password_hash($filter['password'], PASSWORD_DEFAULT);
        }
        $condition = " id=". $user_id;
        // echo '<pre>';
        // print_r($dataUpdate);
        // echo '</pre>';
        // die();

       $updateStatus = update('users', $dataUpdate,$condition);

        if($updateStatus){
            setSessionFlash('msg', 'Cập nhật thành công');
            setSessionFlash('msg_type', 'success');
            redirect('?module=users&action=profile');
        }else {
            setSessionFlash('msg', 'Cập nhật thất bại');
        setSessionFlash('msg_type', 'danger');
        }

    }else {
        setSessionFlash('msg', 'Vui lòng kiểm tra lại dữ liệu nhập vào');
        setSessionFlash('msg_type', 'danger');
        setSessionFlash('oldData', $filter);
        setSessionFlash('errors', $errors);

    }
}
$msg = getSessionFlash('msg');
$msg_type = getSessionFlash('msg_type');

$oldData = getSessionFlash('oldData');
if(!empty($detailUser)){
    $oldData = $detailUser;
}
$errorsArr =  getSessionFlash('errors');
?>
<div class="container add-user">
    <h2>Thông tin tài khoản</h2>
    <hr>
    <?php 
                if(!empty($msg) && !empty($msg_type)){
                    getMsg($msg,$msg_type);
                }
                
                ?>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-6 pb-3">
                <label for="fullname">Họ và tên</label>
                <input id="fullname" name="fullname" type="text" class="form-control" value="<?php 
                        if(!empty($oldData)){
                            echo oldData($oldData, 'fullname');
                        }
                        ?>" placeholder="Nhập họ và tên">
                <?php 
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'fullname');
                        }
                        ?>
            </div>
            <div class="col-6 pb-3">
                <label for="email">Email</label>
                <input id="email" name="email" type="text" class="form-control" value="<?php 
                        if(!empty($oldData)){
                            echo oldData($oldData, 'email');
                        }
                        ?>" placeholder="Nhập email">
                <?php 
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'email');
                        }
                        ?>
            </div>
            <div class="col-6 pb-3">
                <label for="phone">Số điện thoại</label>
                <input id="phone" name="phone" type=" text" class="form-control" value="<?php 
                        if(!empty($oldData)){
                            echo oldData($oldData, 'phone');
                        }
                        ?>" placeholder="Nhập số điện thoại">
                <?php 
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'phone');
                        }
                        ?>
            </div>
            <div class="col-6 pb-3">
                <label for="password">Mật khẩu</label>
                <input id="password" name="password" type="password" class="form-control" value="<?php 
                        if(!empty($oldData)){
                            echo oldData($oldData, 'password');
                        }
                        ?>" placeholder="Nhập mật khẩu">
                <?php 
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'password');
                        }
                        ?>
            </div>
            <div class="col-6 pb-3">
                <label for="address">Địa chỉ</label>
                <input id="address" name="address" type="text" class="form-control" value="<?php 
                        if(!empty($oldData)){
                            echo oldData($oldData, 'address');
                        }
                        ?>" placeholder="Nhập địa chỉ">
            </div>
            <div class="col-6 pb-3">
                <label for="avatar">Ảnh đại diện</label>
                <input id="avatar" name="avatar" type="file" class="form-control" placeholder="Thay ảnh đại diện">
                <img width="200px" id="previewImage" class="preview-image p-3"
                    src="<?php echo ($oldData['avatar']) ? $oldData['avatar'] : false;?>" alt="">
            </div>

        </div>
        <button type="submit" class="btn btn-success px-4" style="min-width: 200px;">Xác nhận</button>
    </form>
</div>

<?php
layout('footer');
?>
<script>
const thumbInput = document.getElementById('avatar');
const previewImg = document.getElementById('previewImage');

thumbInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.setAttribute('src', e.target.result);
            previewImg.style.display = 'block!important';
        }
        reader.readAsDataURL(file);
    } else {
        previewImg.style.display = 'none';
    }

});
</script>