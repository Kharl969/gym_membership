<?php
declare(strict_types=1);

final class Transaction
{
    public static function ensureHistoryTable(PDO $connection): bool
    {
        try {
            $connection->exec('CREATE TABLE IF NOT EXISTS gym_transactions (
                transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                action_type VARCHAR(20) NOT NULL,
                description VARCHAR(255) NOT NULL,
                performed_by VARCHAR(120) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            return true;
        } catch (Throwable $e) {
            error_log('Could not prepare transaction history: ' . $e->getMessage());
            return false;
        }
    }

    public static function getHistory(PDO $connection, int $limit = 100): array
    {
        if (!self::ensureHistoryTable($connection)) {
            return [];
        }
        $limit = max(1, min($limit, 250));
        $stmt = $connection->query('SELECT transaction_id, action_type, description, performed_by, created_at FROM gym_transactions ORDER BY transaction_id DESC LIMIT ' . $limit);
        return $stmt->fetchAll();
    }

    public static function run(PDO $connection, callable $operation, ?array $history = null): bool
    {
        if (!self::ensureHistoryTable($connection)) {
            return false;
        }
        try {
            $connection->beginTransaction();
            $success = (bool) $operation();
            if (!$success) {
                $connection->rollBack();
                return false;
            }
            if ($history !== null) {
                $stmt = $connection->prepare('INSERT INTO gym_transactions (action_type, description, performed_by) VALUES (:action_type, :description, :performed_by)');
                $stmt->execute([
                    'action_type' => $history['action_type'],
                    'description' => $history['description'],
                    'performed_by' => $history['performed_by'],
                ]);
            }
            $connection->commit();
            return true;
        } catch (Throwable $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            error_log('Database transaction failed: ' . $e->getMessage());
            return false;
        }
    }
}