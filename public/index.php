<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;
use CT275\Labs\Paginator;

// Khởi tạo Contact
$contact = new Contact($PDO);

// Số contact hiển thị trên mỗi trang
$limit = isset($_GET['limit']) && is_numeric($_GET['limit'])
    ? (int) $_GET['limit']
    : 5;

// Trang hiện tại
$page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? (int) $_GET['page']
    : 1;

// Không cho phép limit nhỏ hơn 1
if ($limit < 1) {
    $limit = 5;
}

// Không cho phép page nhỏ hơn 1
if ($page < 1) {
    $page = 1;
}

// Tạo Paginator
$paginator = new Paginator(
    $limit,
    $contact->count(),
    $page
);

// Lấy danh sách contact theo trang
$contacts = $contact->paginate(
    $paginator->recordOffset,
    $paginator->recordsPerPage
);

// Lấy danh sách số trang
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

        <!-- Add Contact -->
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
                  <a
                    href="/delete.php?id=<?= $contact->id ?>"
                    class="btn btn-xs btn-danger"
                    title="Delete"
                  >
                    <i class="fa fa-trash"></i>
                    Delete
                  </a>

                </td>

              </tr>

            <?php endforeach; ?>

          </tbody>

        </table>
        <!-- Table Ends Here -->


        <!-- Pagination -->
        <nav class="d-flex justify-content-center">

          <ul class="pagination">

            <!-- Previous Page -->
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


            <!-- Next Page -->
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
  </script>

</body>

</html>