<?php
declare(strict_types=1);

require_once __DIR__ . '/Transaction.php';

final class Member
{
    public function __construct(private PDO $conn)
    {
    }

    public function addMember(string $firstName, string $lastName, string $contact, string $email, string $date, string $trainerId, string $performedBy = 'System'): bool
    {
        return Transaction::run($this->conn, function () use ($firstName, $lastName, $contact, $email, $date, $trainerId): bool {
            $stmt = $this->conn->prepare('INSERT INTO Member (trainer_id, first_name, last_name, contact_number, email, date_registered) VALUES (:trainer_id, :first_name, :last_name, :contact_number, :email, :date_registered)');
            return $stmt->execute(['trainer_id' => $trainerId, 'first_name' => $firstName, 'last_name' => $lastName, 'contact_number' => $contact, 'email' => $email, 'date_registered' => $date]);
        }, ['action_type' => 'REGISTER', 'description' => 'Registered member: ' . trim($firstName . ' ' . $lastName), 'performed_by' => $performedBy]);
    }

    public function getMembers(): array
    {
        return $this->conn->query('SELECT * FROM Member ORDER BY member_id DESC')->fetchAll();
    }

    public function updateMember(string $id, string $firstName, string $lastName, string $contact, string $email, string $date, string $trainerId, string $performedBy = 'System'): bool
    {
        return Transaction::run($this->conn, function () use ($id, $firstName, $lastName, $contact, $email, $date, $trainerId): bool {
            $stmt = $this->conn->prepare('UPDATE Member SET trainer_id=:trainer_id, first_name=:first_name, last_name=:last_name, contact_number=:contact_number, email=:email, date_registered=:date_registered WHERE member_id=:member_id');
            return $stmt->execute(['member_id' => $id, 'trainer_id' => $trainerId, 'first_name' => $firstName, 'last_name' => $lastName, 'contact_number' => $contact, 'email' => $email, 'date_registered' => $date]);
        }, ['action_type' => 'UPDATE', 'description' => 'Updated member: ' . trim($firstName . ' ' . $lastName), 'performed_by' => $performedBy]);
    }

    public function deleteMember(string $id, string $performedBy = 'System'): bool
    {
        return Transaction::run($this->conn, function () use ($id): bool {
            $stmt = $this->conn->prepare('DELETE FROM Member WHERE member_id=:member_id');
            return $stmt->execute(['member_id' => $id]);
        }, ['action_type' => 'DELETE', 'description' => 'Deleted member record #' . $id, 'performed_by' => $performedBy]);
    }

    public function searchMembers(string $keyword): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM Member WHERE first_name LIKE :first_name OR last_name LIKE :last_name OR contact_number LIKE :contact_number OR email LIKE :email ORDER BY member_id DESC');
        $pattern = '%' . $keyword . '%';
        $stmt->execute(['first_name' => $pattern, 'last_name' => $pattern, 'contact_number' => $pattern, 'email' => $pattern]);
        return $stmt->fetchAll();
    }
}