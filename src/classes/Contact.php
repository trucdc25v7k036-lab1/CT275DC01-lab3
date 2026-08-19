<?php

namespace CT275\Labs;

use PDO;

class Contact
{
    private ?PDO $db;

    public int $id = -1;
    public $name;
    public $phone;
    public $notes;
    public $avatar;
    public $created_at;
    public $updated_at;

    public function __construct(?PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Điền dữ liệu cho Contact
     */
    public function fill(array $data): Contact
    {
        $this->name = $data['name'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->notes = $data['notes'] ?? '';
        $this->avatar = $data['avatar'] ?? '';

        return $this;
    }

    /**
     * Kiểm tra dữ liệu Contact
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Validate name
        $name = trim($data['name'] ?? '');

        if (!$name) {
            $errors['name'] = 'Invalid name.';
        }

        // Validate phone
        $validPhone = preg_match(
            '/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})\b$/',
            $data['phone'] ?? ''
        );

        if (!$validPhone) {
            $errors['phone'] = 'Invalid phone number.';
        }

        // Validate notes
        $notes = trim($data['notes'] ?? '');

        if (strlen($notes) > 255) {
            $errors['notes'] = 'Notes must be at most 255 characters.';
        }

        return $errors;
    }

    /**
     * Lấy tất cả Contact
     */
    public function all(): array
    {
        $contacts = [];

        $statement = $this->db->prepare(
            'SELECT * FROM contacts'
        );

        $statement->execute();

        while ($row = $statement->fetch()) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }

        return $contacts;
    }

    /**
     * Điền dữ liệu từ database vào object Contact
     */
    protected function fillFromDbRow(array $row): Contact
    {
        $this->id = (int) $row['id'];
        $this->name = $row['name'];
        $this->phone = $row['phone'];
        $this->notes = $row['notes'];
        $this->avatar = $row['avatar'] ?? '';
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];

        return $this;
    }

    /**
     * Đếm tổng số Contact
     */
    public function count(): int
    {
        $statement = $this->db->prepare(
            'SELECT count(*) FROM contacts'
        );

        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    /**
     * Lấy Contact theo phân trang
     */
    public function paginate(
        int $offset = 0,
        int $limit = 10
    ): array {
        $contacts = [];

        $statement = $this->db->prepare(
            'SELECT * FROM contacts
             LIMIT :limit
             OFFSET :offset'
        );

        $statement->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $statement->execute();

        while ($row = $statement->fetch()) {
            $contact = new Contact($this->db);
            $contact->fillFromDbRow($row);
            $contacts[] = $contact;
        }

        return $contacts;
    }

    /**
     * Thêm mới hoặc cập nhật Contact
     */
    public function save(): bool
    {
        $result = false;

        /*
         * Nếu id >= 0:
         * Contact đã tồn tại -> UPDATE
         */
        if ($this->id >= 0) {

            $statement = $this->db->prepare(
                'UPDATE contacts
                 SET name = :name,
                     phone = :phone,
                     notes = :notes,
                     avatar = :avatar,
                     updated_at = now()
                 WHERE id = :id'
            );

            $result = $statement->execute([
                'name' => $this->name,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'avatar' => $this->avatar,
                'id' => $this->id
            ]);

        } else {

            /*
             * Contact mới -> INSERT
             */
            $statement = $this->db->prepare(
                'INSERT INTO contacts
                    (name, phone, notes, avatar, created_at, updated_at)
                 VALUES
                    (:name, :phone, :notes, :avatar, now(), now())'
            );

            $result = $statement->execute([
                'name' => $this->name,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'avatar' => $this->avatar
            ]);

            /*
             * Lấy id của Contact vừa thêm
             */
            if ($result) {
                $this->id = (int) $this->db->lastInsertId();
            }
        }

        return $result;
    }

    /**
     * Tìm Contact theo id
     */
    public function find(int $id): ?Contact
    {
        $statement = $this->db->prepare(
            'SELECT * FROM contacts
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id
        ]);

        if ($row = $statement->fetch()) {
            $this->fillFromDbRow($row);

            return $this;
        }

        return null;
    }

    /**
     * Xóa Contact
     */
    public function delete(): bool
    {
        $statement = $this->db->prepare(
            'DELETE FROM contacts
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $this->id
        ]);
    }
}