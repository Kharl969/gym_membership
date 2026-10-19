<?php
/** @var array<int, array<string, mixed>> $members Provided by dashboard/index.php. */
foreach ($members as $row): ?>
<div class="modal fade" id="deleteMemberModal<?= (int) $row['member_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <input type="hidden" name="member_id" value="<?= (int) $row['member_id'] ?>">
                <div class="modal-body">
                    Are you sure you want to delete
                    <strong><?= escape((string) $row['first_name'] . ' ' . (string) $row['last_name']) ?></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_member" class="btn btn-danger">Delete Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>