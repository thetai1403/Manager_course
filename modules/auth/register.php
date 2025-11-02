<?php
if(!defined('_TAI')){
    die('Truy cập không hợp lệ');
}
$data = [
    'title' => 'Đăng ký tài khoản'
];



layout('header-auth',$data);


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

    //Validate phone
    if(empty($filter['phone'])){
        $errors['phone']['required'] = 'Số điện thoại bắt buộc phải nhập';
    }else {
        if (!isPhone($filter['phone'])){
            $errors['phone']['isPhone'] = 'Số điện thoại không đúng định dạng';
        }
    }

    //Validate password
    if(empty(trim($filter['password']))){
        $errors['password']['required'] = 'Mật khẩu bắt buộc phải nhập';
    }else {
        if (strlen(trim($filter['password']))<6){
            $errors['password']['length'] = 'Mật khẩu phải lớn hơn 6 kí tự';
        }
    }

    //Validate confirm password
    if(empty(trim($filter['password']))){
        $errors['confirm_password']['required'] = 'Vui lòng nhập lại mật khẩu';
    }else {
        if (trim($filter['password']) !== trim($filter['confirm_password'])){
            $errors['confirm_password']['like'] = 'Mật khẩu nhập vào không khớp';
        }
    }

    if(empty($errors)) {
        //table: users, data
        $active_token = sha1(uniqid().time());
        $data = [
            'fullname' => $filter['fullname'],
            'phone' => $filter['phone'],
            'password' => password_hash($filter['password'], PASSWORD_DEFAULT),
            'email' => $filter['email'],
            'active_token' => $active_token,
            'group_id' => 1,
            'created_at' => date('Y:m:d H:i:s')
        ];

        $insertStatus = insert('users', $data);

        if($insertStatus){
            $emailTo = $filter['email'];
            $subject = 'Kích hoạt tài khoản hệ thống Tai!!';
            $content = 'Chúc mừng bạn đã đăng ký thành công tài khoản tại Tai. </br>';
            $content .= 'Để kích hoạt tài khoản bạn hãy click vào đường link bên dưới: </br>';
            $content .= _HOST_URL . '/?module=auth&action=active&token='.$active_token.'</br>';
            $content .= 'Cảm ơn bạn đã ủng hộ Tai!!!';
            sendMail($emailTo, $subject, $content);

            setSessionFlash('msg', 'Đăng ký thành công vui lòng kích hoạt tài khoản');
            setSessionFlash('msg_type', 'success');
        } else {
            setSessionFlash('msg', 'Đăng ký không thành công vui lòng thử lại sau');
            setSessionFlash('msg_type', 'danger');
        }
    } else {
        setSessionFlash('msg', 'Vui lòng kiểm tra lại dữ liệu nhập vào');
        setSessionFlash('msg_type', 'danger');
        setSessionFlash('oldData', $filter);
        setSessionFlash('errors', $errors);

    }

    $msg = getSessionFlash('msg');
    $msg_type = getSessionFlash('msg_type');
    $oldData = getSessionFlash('oldData');
    $errorsArr =  getSessionFlash('errors');

}
?>
<section class="vh-100">
    <div class="container-fluid h-custom">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
                <img src="<?php echo _HOST_URL_TEMPLATES;?>/assets/image/draw2.webp" class="img-fluid"
                    alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                <?php 
                if(!empty($msg) && !empty($msg_type)){
                    getMsg($msg,$msg_type);
                }
                
                ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                        <h2 class="fw-normal mb-5 me-3">Đăng ký tài khoản</h2>

                    </div>


                    <!-- Name, Email, SDT, Mật khẩu, Nhập lại mk  -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="fullname" type="text" value="<?php 
                        if(!empty($oldData)){
                            echo oldData($oldData, 'fullname');
                        }
                        ?>" class="form-control form-control-lg" placeholder="Họ tên" />
                        <?php 
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'fullname');
                        }
                        ?>


                    </div>
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="email" type="text" value="<?php
                        if(!empty($oldData)){
                        echo oldData($oldData, 'email');
                        }
                        ?>" class="form-control form-control-lg" placeholder="Địa chỉ email" />
                        <?php
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'email');
                        }
                        ?>
                    </div>
                    <!-- Số dien thoại -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="phone" type="text" value="<?php 
                        if(!empty($oldData)){
                            echo oldData($oldData, 'phone');
                        }
                        ?>" class="form-control form-control-lg" placeholder="Nhập số điện thoại " />
                        <?php if(!empty($errorsArr)){
                            echo formError($errorsArr, 'phone');
                        }
                        ?>
                    </div>

                    <!-- Password input -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input name="password" type="password" id="form3Example4" class="form-control form-control-lg"
                            placeholder="Nhập mật khẩu" />
                        <?php 
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'password');
                        }
                        ?>
                    </div>

                    <!-- Nhập lại mk -->

                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="confirm_password" type="password" class="form-control form-control-lg"
                            placeholder="Nhập lại mật khẩu" />
                        <?php 
                        if(!empty($errorsArr)){
                            echo formError($errorsArr, 'confirm_password');
                        }
                        ?>
                    </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Đăng ký</button>
                        <p class="small fw-bold mt-2 pt-1 mb-0">Bạn đã có tài khoản? <a
                                href="<?php echo _HOST_URL;?>?module=auth&action=login" class="link-danger">Đăng nhập
                                ngay</a></p>
                    </div>

                </form>
            </div>
        </div>
    </div>

</section>

<?php 
layout('footer');