<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;
use CT275\Labs\Paginator;

$contactModel = new Contact($PDO);

// Số contact trên mỗi trang
$limit = isset($_GET['limit']) && is_numeric($_GET['limit'])
    ? (int) $_GET['limit']
    : 5;

// Trang hiện tại
$page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? (int) $_GET['page']
    : 1;

// Kiểm tra limit
if ($limit < 1) {
    $limit = 5;
}

// Kiểm tra page
if ($page < 1) {
    $page = 1;
}

// Tạo Paginator
$paginator = new Paginator(
    $limit,
    $contactModel->count(),
    $page
);

// Lấy contacts theo trang
$contacts = $contactModel->paginate(
    $paginator->recordOffset,
    $paginator->recordsPerPage
);

// Lấy danh sách trang
$pages = $paginator->getPages(3);

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>

<?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

<!-- Main Page Content -->
<div class="container">

    <?php
    $subtitle = 'View your all contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
        <div class="col-12">

            <!-- New Contact -->
            <a
                href="/add.php"
                class="btn btn-primary mb-3"
            >
                <i class="fa fa-plus"></i>
                New Contact
            </a>


            <!-- ========================= -->
            <!-- CONTACT TABLE -->
            <!-- ========================= -->

            <table
                id="contacts"
                class="table table-striped table-bordered align-middle"
            >

                <thead>
                    <tr>

                        <th scope="col">
                            Avatar
                        </th>

                        <th scope="col">
                            Name
                        </th>

                        <th scope="col">
                            Phone
                        </th>

                        <th scope="col">
                            Date Created
                        </th>

                        <th scope="col">
                            Notes
                        </th>

                        <th scope="col">
                            Actions
                        </th>

                    </tr>
                </thead>


                <tbody>

                <?php if (empty($contacts)) : ?>

                    <tr>
                        <td
                            colspan="6"
                            class="text-center"
                        >
                            No contacts found.
                        </td>
                    </tr>

                <?php else : ?>

                    <?php foreach ($contacts as $contact) : ?>

                        <tr>

                            <!-- ========================= -->
                            <!-- AVATAR -->
                            <!-- ========================= -->

                            <td>

                                <?php if (!empty($contact->avatar)) : ?>

                                    <img
                                        src="<?= html_escape($contact->avatar) ?>"
                                        alt="Avatar"
                                        width="60"
                                        height="60"
                                        style="
                                            width: 60px;
                                            height: 60px;
                                            object-fit: cover;
                                            border-radius: 50%;
                                            border: 1px solid #ddd;
                                        "
                                    >

                                <?php else : ?>

                                    <!-- Avatar mặc định -->
                                    <div
                                        style="
                                            width: 60px;
                                            height: 60px;
                                            border-radius: 50%;
                                            background-color: #e9ecef;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            color: #6c757d;
                                            font-size: 24px;
                                        "
                                    >
                                        <i class="fa fa-user"></i>
                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- ========================= -->
                            <!-- NAME -->
                            <!-- ========================= -->

                            <td>
                                <?= html_escape($contact->name) ?>
                            </td>


                            <!-- ========================= -->
                            <!-- PHONE -->
                            <!-- ========================= -->

                            <td>
                                <?= html_escape($contact->phone) ?>
                            </td>


                            <!-- ========================= -->
                            <!-- DATE CREATED -->
                            <!-- ========================= -->

                            <td>

                                <?= html_escape(
                                    date(
                                        'd-m-Y',
                                        strtotime($contact->created_at)
                                    )
                                ) ?>

                            </td>


                            <!-- ========================= -->
                            <!-- NOTES -->
                            <!-- ========================= -->

                            <td>
                                <?= html_escape($contact->notes) ?>
                            </td>


                            <!-- ========================= -->
                            <!-- ACTIONS -->
                            <!-- ========================= -->

                            <td>

                                <div
                                    class="d-flex justify-content-center"
                                >

                                    <!-- Edit -->
                                    <a
                                        href="/edit.php?id=<?= $contact->id ?>"
                                        class="btn btn-sm btn-warning me-1"
                                        title="Edit"
                                    >
                                        <i class="fa fa-pencil"></i>
                                        Edit
                                    </a>


                                    <!-- Delete -->
                                    <form
                                        action="/delete.php"
                                        method="POST"
                                        class="ms-1"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $contact->id ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                            name="delete-contact"
                                            title="Delete"
                                        >
                                            <i class="fa fa-trash"></i>
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>


            <!-- ========================= -->
            <!-- PAGINATION -->
            <!-- ========================= -->

            <nav class="d-flex justify-content-center">

                <ul class="pagination">

                    <?php
                    $prevPage = $paginator->getPrevPage();
                    ?>

                    <!-- Previous -->
                    <li
                        class="page-item <?= $prevPage === false ? 'disabled' : '' ?>"
                    >

                        <a
                            class="page-link"
                            href="/?page=<?= $prevPage ?: 1 ?>&limit=<?= $limit ?>"
                        >
                            <span>&laquo;</span>
                        </a>

                    </li>


                    <!-- Page Numbers -->
                    <?php foreach ($pages as $pageNumber) : ?>

                        <li
                            class="page-item
                            <?= $paginator->currentPage === $pageNumber
                                ? 'active'
                                : '' ?>"
                        >

                            <a
                                class="page-link"
                                href="/?page=<?= $pageNumber ?>&limit=<?= $limit ?>"
                            >
                                <?= $pageNumber ?>
                            </a>

                        </li>

                    <?php endforeach; ?>


                    <?php
                    $nextPage = $paginator->getNextPage();
                    ?>

                    <!-- Next -->
                    <li
                        class="page-item <?= $nextPage === false ? 'disabled' : '' ?>"
                    >

                        <a
                            class="page-link"
                            href="/?page=<?= $nextPage ?: $paginator->currentPage ?>&limit=<?= $limit ?>"
                        >
                            <span>&raquo;</span>
                        </a>

                    </li>

                </ul>

            </nav>

        </div>
    </div>

</div>


<!-- ========================= -->
<!-- DELETE CONFIRMATION MODAL -->
<!-- ========================= -->

<div
    id="delete-confirm"
    class="modal fade"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4 class="modal-title">
                    Confirmation
                </h4>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                >
                </button>

            </div>


            <div class="modal-body">
                Do you want to delete this contact?
            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    data-bs-dismiss="modal"
                    class="btn btn-danger"
                    id="delete"
                >
                    Delete
                </button>

                <button
                    type="button"
                    data-bs-dismiss="modal"
                    class="btn btn-secondary"
                >
                    Cancel
                </button>

            </div>

        </div>

    </div>

</div>


<?php include_once __DIR__ . '/../src/partials/footer.php'; ?>


<!-- ========================= -->
<!-- DELETE CONFIRMATION SCRIPT -->
<!-- ========================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const deleteButtons = document.querySelectorAll(
        'button[name="delete-contact"]'
    );

    const modalElement =
        document.getElementById('delete-confirm');

    const deleteModal =
        new bootstrap.Modal(modalElement);

    const confirmDeleteButton =
        document.getElementById('delete');

    let selectedForm = null;


    deleteButtons.forEach(function (button) {

        button.addEventListener('click', function (event) {

            // Không submit ngay
            event.preventDefault();

            // Lấy form
            selectedForm =
                button.closest('form');


            // Lấy tên contact
            const row =
                button.closest('tr');

            const nameCell =
                row.querySelector(
                    'td:nth-child(2)'
                );


            if (nameCell) {

                modalElement
                    .querySelector('.modal-body')
                    .textContent =
                    'Do you want to delete "'
                    + nameCell.textContent.trim()
                    + '"?';

            }


            // Hiển thị modal
            deleteModal.show();

        });

    });


    /*
     * Xác nhận Delete
     */
    confirmDeleteButton.addEventListener(
        'click',
        function () {

            if (selectedForm) {

                selectedForm.submit();

                selectedForm = null;

            }

        }
    );

});

</script>

</body>

</html>