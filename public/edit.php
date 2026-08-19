<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);

// Lấy id từ URL hoặc form
$id = isset($_REQUEST['id'])
    ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT)
    : false;

// Nếu id không hợp lệ hoặc không tìm thấy contact
if ($id === false || ($contact = $contact->find($id)) === null) {
    redirect('/');
}

$errors = [];

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ];

    // Kiểm tra dữ liệu
    $errors = $contact->validate($contactData);

    if (empty($errors)) {

        // Cập nhật dữ liệu vào object
        $contact->fill($contactData);

        // Lưu thay đổi vào database
        $contact->save();

        // Quay về trang danh sách
        redirect('/');
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>

  <?php include_once __DIR__ . '/../src/partials/navbar.php' ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php
    $subtitle = 'Update your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-12">

        <form method="post" class="col-md-6 offset-md-3">

          <!-- ID -->
          <input
            type="hidden"
            name="id"
            value="<?= $contact->id ?>"
          >

          <!-- Name -->
          <div class="mb-3">

            <label for="name" class="form-label">
              Name
            </label>

            <input
              type="text"
              name="name"
              class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
              maxlength="255"
              id="name"
              placeholder="Enter Name"
              value="<?= isset($_POST['name'])
                ? html_escape($_POST['name'])
                : html_escape($contact->name) ?>"
            />

            <?php if (isset($errors['name'])) : ?>

              <span class="invalid-feedback">
                <strong>
                  <?= html_escape($errors['name']) ?>
                </strong>
              </span>

            <?php endif ?>

          </div>


          <!-- Phone -->
          <div class="mb-3">

            <label for="phone" class="form-label">
              Phone Number
            </label>

            <input
              type="text"
              name="phone"
              class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
              maxlength="15"
              id="phone"
              placeholder="Enter Phone"
              value="<?= isset($_POST['phone'])
                ? html_escape($_POST['phone'])
                : html_escape($contact->phone) ?>"
            />

            <?php if (isset($errors['phone'])) : ?>

              <span class="invalid-feedback">
                <strong>
                  <?= html_escape($errors['phone']) ?>
                </strong>
              </span>

            <?php endif ?>

          </div>


          <!-- Notes -->
          <div class="mb-3">

            <label for="notes" class="form-label">
              Notes
            </label>

            <textarea
              name="notes"
              id="notes"
              maxlength="255"
              class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>"
              placeholder="Enter notes (maximum character limit: 255)"
            ><?= isset($_POST['notes'])
              ? html_escape($_POST['notes'])
              : html_escape($contact->notes) ?></textarea>

            <?php if (isset($errors['notes'])) : ?>

              <span class="invalid-feedback">
                <strong>
                  <?= html_escape($errors['notes']) ?>
                </strong>
              </span>

            <?php endif ?>

          </div>


          <!-- Submit -->
          <button
            type="submit"
            name="submit"
            class="btn btn-primary"
          >
            Update Contact
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

  <?php include_once __DIR__ . '/../src/partials/footer.php' ?>

</body>

</html>