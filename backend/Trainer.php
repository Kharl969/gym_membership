<?php
declare(strict_types=1);

require_once __DIR__ . '/Transaction.php';

final class Trainer
{
    public function __construct(private PDO $conn)
    {
    }

    public function getTrainers(): array
    {
        return $this->conn->query('SELECT trainer_id, first_name, last_name, contact_number, specialty FROM trainer ORDER BY trainer_id DESC')->fetchAll();
    }

    public function addTrainer(string $firstName, string $lastName, string $contactNumber, string $specialty, string $performedBy): bool
    {
        $fullName = trim($firstName . ' ' . $lastName);
        return Transaction::run($this->conn, function () use ($firstName, $lastName, $contactNumber, $specialty): bool {
            $stmt = $this->conn->prepare('INSERT INTO trainer (first_name, last_name, contact_number, specialty) VALUES (:first_name, :last_name, :contact_number, :specialty)');
            return $stmt->execute([
                'first_name' => trim($firstName),
                'last_name' => trim($lastName),
                'contact_number' => trim($contactNumber),
                'specialty' => trim($specialty),
            ]);
        }, [
            'action_type' => 'TRAINER',
            'description' => 'Registered trainer: ' . $fullName,
            'performed_by' => $performedBy,
        ]);
    }
}
