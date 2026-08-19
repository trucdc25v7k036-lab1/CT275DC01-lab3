<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$errors = [];
$contact = new Contact($PDO);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lấy dữ liệu từ form
    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'avatar' => ''
    ];

    // Validate name, phone, notes
    $errors = $contact->validate($contactData);

    /*
     * =========================
     * XỬ LÝ UPLOAD AVATAR
     * =========================
     */

    if (
        isset($_FILES['avatar']) &&
        $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        // Kiểm tra upload có lỗi không
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {

            $errors['avatar'] = 'Upload avatar failed.';

        } else {

            // Giới hạn dung lượng: 2MB
            $maxSize = 2 * 1024 * 1024;

            if ($_FILES['avatar']['size'] > $maxSize) {

                $errors['avatar'] = 'Avatar must be smaller than 2MB.';

            } else {

                // Kiểm tra MIME type thật của file
                $finfo = new finfo(FILEINFO_MIME_TYPE);

                $mimeType = $finfo->file(
                    $_FILES['avatar']['tmp_name']
                );

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowedTypes[$mimeType])) {

                    $errors['avatar'] =
                        'Avatar must be a JPG, PNG, GIF or WEBP image.';

                } else {

                    /*
                     * Tạo tên file ngẫu nhiên
                     * để tránh trùng tên ảnh.
                     */
                    $extension = $allowedTypes[$mimeType];

                    $fileName =
                        bin2hex(random_bytes(16))
                        . '.'
                        . $extension;

                    /*
                     * Đường dẫn thư mục lưu ảnh
                     */
                    $uploadDir =
                        __DIR__
                        . '/uploads/avatars/';

                    /*
                     * Nếu thư mục chưa tồn tại
                     * thì tạo thư mục.
                     */
                    if (!is_dir($uploadDir)) {

                        mkdir(
                            $uploadDir,
                            0777,
                            true
                        );
                    }

                    /*
                     * Đường dẫn file cuối cùng
                     */
                    $destination =
                        $uploadDir
                        . $fileName;

                    /*
                     * Di chuyển file upload
                     */
                    if (
                        move_uploaded_file(
                            $_FILES['avatar']['tmp_name'],
                            $destination
                        )
                    ) {

                        /*
                         * Lưu đường dẫn tương đối
                         * vào database.
                         */
                        $contactData['avatar'] =
                            '/uploads/avatars/'
                            . $fileName;

                    } else {

                        $errors['avatar'] =
                            'Cannot save avatar.';
                    }
                }
            }
        }
    }

    /*
     * =========================
     * LƯU CONTACT
     * =========================
     */

    if (empty($errors)) {

        $contact->fill($contactData);

        if ($contact->save()) {

            redirect('/');
        } else {

            $errors['general'] =
                'Cannot save contact.';
        }
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>

<?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

<!-- Main Page Content -->
<div class="container">

    <?php
    $subtitle = 'Add your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
        <div class="col-12">

            <form
                method="post"
                enctype="multipart/form-data"
                class="col-md-6 offset-md-3"
            >

                <!-- General Error -->
                <?php if (isset($errors['general'])) : ?>

                    <div class="alert alert-danger">
                        <?= html_escape($errors['general']) ?>
                    </div>

                <?php endif; ?>


                <!-- ========================= -->
                <!-- AVATAR -->
                <!-- ========================= -->

                <div class="mb-3">

                    <label
                        for="avatar"
                        class="form-label"
                    >
                        Avatar
                    </label>

                    <input
                        type="file"
                        name="avatar"
                        id="avatar"
                        class="form-control<?= isset($errors['avatar']) ? ' is-invalid' : '' ?>"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                    >

                    <?php if (isset($errors['avatar'])) : ?>

                        <div class="invalid-feedback">
                            <strong>
                                <?= html_escape($errors['avatar']) ?>
                            </strong>
                        </div>

                    <?php endif; ?>

                    <!-- Preview Avatar -->
                    <div class="mt-3">

                        <img
                            id="avatar-preview"
                            src=""
                            alt="Avatar Preview"
                            style="
                                display: none;
                                width: 150px;
                                height: 150px;
                                object-fit: cover;
                                border-radius: 50%;
                                border: 1px solid #ddd;
                            "
                        >

                    </div>

                </div>


                <!-- ========================= -->
                <!-- NAME -->
                <!-- ========================= -->

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >
                        Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                        maxlength="255"
                        id="name"
                        placeholder="Enter Name"
                        value="<?= isset($_POST['name']) ? html_escape($_POST['name']) : '' ?>"
                    >

                    <?php if (isset($errors['name'])) : ?>

                        <span class="invalid-feedback">
                            <strong>
                                <?= html_escape($errors['name']) ?>
                            </strong>
                        </span>

                    <?php endif; ?>

                </div>


                <!-- ========================= -->
                <!-- PHONE -->
                <!-- ========================= -->

                <div class="mb-3">

                    <label
                        for="phone"
                        class="form-label"
                    >
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
                        maxlength="15"
                        id="phone"
                        placeholder="Enter Phone"
                        value="<?= isset($_POST['phone']) ? html_escape($_POST['phone']) : '' ?>"
                    >

                    <?php if (isset($errors['phone'])) : ?>

                        <span class="invalid-feedback">
                            <strong>
                                <?= html_escape($errors['phone']) ?>
                            </strong>
                        </span>

                    <?php endif; ?>

                </div>


                <!-- ========================= -->
                <!-- NOTES -->
                <!-- ========================= -->

                <div class="mb-3">

                    <label
                        for="notes"
                        class="form-label"
                    >
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>"
                        placeholder="Enter notes (maximum character limit: 255)"
                        maxlength="255"
                    ><?= isset($_POST['notes']) ? html_escape($_POST['notes']) : '' ?></textarea>

                    <?php if (isset($errors['notes'])) : ?>

                        <span class="invalid-feedback">
                            <strong>
                                <?= html_escape($errors['notes']) ?>
                            </strong>
                        </span>

                    <?php endif; ?>

                </div>


                <!-- ========================= -->
                <!-- SUBMIT -->
                <!-- ========================= -->

                <button
                    type="submit"
                    name="submit"
                    class="btn btn-primary"
                >
                    Add Contact
                </button>

                <a
                    href="/"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </form>

        </div>
    </div>

</div>


<?php include_once __DIR__ . '/../src/partials/footer.php'; ?>


<!-- ========================= -->
<!-- AVATAR PREVIEW JAVASCRIPT -->
<!-- ========================= -->

<script>

const avatarInput = document.getElementById('avatar');
const avatarPreview = document.getElementById('avatar-preview');

avatarInput.addEventListener('change', function () {

    const file = this.files[0];

    if (!file) {
        avatarPreview.style.display = 'none';
        avatarPreview.src = '';
        return;
    }

    /*
     * Chỉ preview những file là image
     */
    if (!file.type.startsWith('image/')) {

        avatarPreview.style.display = 'none';
        avatarPreview.src = '';

        return;
    }

    /*
     * Tạo URL tạm thời cho ảnh
     */
    const imageUrl = URL.createObjectURL(file);

    avatarPreview.src = imageUrl;
    avatarPreview.style.display = 'block';
});

</script>

</body>

</html>