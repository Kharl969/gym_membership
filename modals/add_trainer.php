<div class="modal fade" id="addTrainerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Trainer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <input type="hidden" name="view" value="trainers">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label" for="trainer_first_name">First name</label><input class="form-control" id="trainer_first_name" name="first_name" maxlength="50" autocomplete="given-name" required></div>
                    <div class="mb-3"><label class="form-label" for="trainer_last_name">Last name</label><input class="form-control" id="trainer_last_name" name="last_name" maxlength="50" autocomplete="family-name" required></div>
                    <div class="mb-3"><label class="form-label" for="trainer_contact_number">Contact number</label><input class="form-control" id="trainer_contact_number" name="contact_number" maxlength="20" autocomplete="tel" required></div>
                    <div class="mb-3"><label class="form-label" for="trainer_specialty">Specialty</label><input class="form-control" id="trainer_specialty" name="specialty" maxlength="100" placeholder="e.g. Strength training" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_trainer" class="btn btn-primary add-btn">Add Trainer</button>
                </div>
            </form>
        </div>
    </div>
</div>
