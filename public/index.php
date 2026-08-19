<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;
use CT275\Labs\Paginator;

$contact = new Contact($PDO);

// Số bản ghi trên mỗi trang
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
    $contact->count(),
    $page
);

// Lấy contacts theo trang
$contacts = $contact->paginate(
    $paginator->recordOffset,
    $paginator->recordsPerPage
);

// Lấy danh sách các trang
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

        <!-- Table Starts Here -->
        <table
          id="contacts"
          class="table table-striped table-bordered"
        >

          <thead>
            <tr>
              <th scope="col">Name</th>
              <th scope="col">Phone</th>
              <th scope="col">Date Created</th>
              <th scope="col">Notes</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>

          <tbody>

            <?php foreach ($contacts as $contact) : ?>

              <tr>

                <!-- Name -->
                <td>
                  <?= html_escape($contact->name) ?>
                </td>

                <!-- Phone -->
                <td>
                  <?= html_escape($contact->phone) ?>
                </td>

                <!-- Date Created -->
                <td>
                  <?= html_escape(
                    date(
                      'd-m-Y',
                      strtotime($contact->created_at)
                    )
                  ) ?>
                </td>

                <!-- Notes -->
                <td>
                  <?= html_escape($contact->notes) ?>
                </td>

                <!-- Actions -->
                <td class="d-flex justify-content-center">

                  <!-- Edit -->
                  <a
                    href="/edit.php?id=<?= $contact->id ?>"
                    class="btn btn-xs btn-warning me-1"
                    title="Edit"
                  >
                    <i class="fa fa-pencil"></i>
                    Edit
                  </a>

                  <!-- Delete -->
                  <form
                    class="ms-1"
                    action="/delete.php"
                    method="POST"
                  >

                    <input
                      type="hidden"
                      name="id"
                      value="<?= $contact->id ?>"
                    >

                    <button
                      type="submit"
                      class="btn btn-xs btn-danger"
                      name="delete-contact"
                      title="Delete"
                    >
                      <i class="fa fa-trash"></i>
                      Delete
                    </button>

                  </form>

                </td>

              </tr>

            <?php endforeach; ?>

          </tbody>

        </table>
        <!-- Table Ends Here -->


        <!-- Pagination -->
        <nav class="d-flex justify-content-center">

          <ul class="pagination">

            <!-- Previous -->
            <li class="page-item">

              <?php
              $prevPage = $paginator->getPrevPage();
              ?>

              <a
                role="button"
                href="/?page=<?= $prevPage ?: 1 ?>&limit=<?= $limit ?>"
                class="page-link"
                <?= $prevPage === false ? 'aria-disabled="true"' : '' ?>
              >
                <span>&laquo;</span>
              </a>

            </li>


            <!-- Page Numbers -->
            <?php foreach ($pages as $pageNumber) : ?>

              <li
                class="page-item <?= $paginator->currentPage === $pageNumber ? 'active' : '' ?>"
              >

                <a
                  role="button"
                  href="/?page=<?= $pageNumber ?>&limit=<?= $limit ?>"
                  class="page-link"
                >
                  <?= $pageNumber ?>
                </a>

              </li>

            <?php endforeach; ?>


            <!-- Next -->
            <li class="page-item">

              <?php
              $nextPage = $paginator->getNextPage();
              ?>

              <a
                role="button"
                href="/?page=<?= $nextPage ?: $paginator->currentPage ?>&limit=<?= $limit ?>"
                class="page-link"
                <?= $nextPage === false ? 'aria-disabled="true"' : '' ?>
              >
                <span>&raquo;</span>
              </a>

            </li>

          </ul>

        </nav>
        <!-- End Pagination -->

      </div>
    </div>

  </div>


  <!-- Delete Confirmation Modal -->
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
            class="btn btn-default"
          >
            Cancel
          </button>

        </div>

      </div>

    </div>

  </div>


  <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>


  <script>

    // Lấy tất cả các nút Delete
    const deleteButtons = document.querySelectorAll(
      'button[name="delete-contact"]'
    );

    deleteButtons.forEach(button => {

      button.addEventListener('click', function (e) {

        // Không submit form ngay lập tức
        e.preventDefault();

        // Lấy form chứa nút Delete
        const form = button.closest('form');

        // Lấy id contact
        const nameTd = button
          .closest('tr')
          .querySelector('td:first-child');

        if (nameTd) {

          document.querySelector('.modal-body').textContent =
            `Do you want to delete "${nameTd.textContent.trim()}"?`;

        }

        // Hàm submit form
        const submitForm = function () {
          form.submit();
        };

        // Nút Delete trong modal
        document
          .getElementById('delete')
          .addEventListener('click', submitForm, {
            once: true
          });

        // Modal
        const modalEl = document.getElementById('delete-confirm');

        const confirmModal = new bootstrap.Modal(
          modalEl,
          {
            backdrop: 'static',
            keyboard: false
          }
        );

        // Hiện modal
        confirmModal.show();

      });

    });

  </script>

</body>

</html>