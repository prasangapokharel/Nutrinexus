<?php ob_start(); ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Referrals</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('admin') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Referrals</li>
    </ol>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Referral Earnings
        </div>
        <div class="card-body">
            <?php if (empty($referralEarnings)): ?>
                <div class="alert alert-info">No referral earnings found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Order</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referralEarnings as $earning): ?>
                                <tr>
                                    <td><?= $earning['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($earning['first_name'] . ' ' . $earning['last_name']) ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($earning['email']) ?></small>
                                    </td>
                                    <td>
                                        <a href="<?= \App\Core\View::url('admin/viewOrder/' . $earning['order_id']) ?>">
                                            <?= htmlspecialchars($earning['invoice']) ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">₹<?= number_format($earning['total_amount'], 2) ?></small>
                                    </td>
                                    <td class="text-end">₹<?= number_format($earning['amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $earning['status'] === 'pending' ? 'warning' : ($earning['status'] === 'paid' ? 'success' : 'danger') ?>">
                                            <?= ucfirst($earning['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y g:i A', strtotime($earning['created_at'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?= $earning['id'] ?>">
                                            Update Status
                                        </button>
                                        
                                        <!-- Status Update Modal -->
                                        <div class="modal fade" id="updateStatusModal<?= $earning['id'] ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel<?= $earning['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="updateStatusModalLabel<?= $earning['id'] ?>">Update Referral Status</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="<?= \App\Core\View::url('admin/updateReferralStatus/' . $earning['id']) ?>" method="post">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="status<?= $earning['id'] ?>" class="form-label">Status</label>
                                                                <select class="form-select" id="status<?= $earning['id'] ?>" name="status" required>
                                                                    <option value="pending" <?= $earning['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                                    <option value="paid" <?= $earning['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                                                    <option value="cancelled" <?= $earning['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                                </select>
                                                            </div>
                                                            <div class="alert alert-warning">
                                                                <strong>Note:</strong> Changing status to "Cancelled" will deduct the amount from the user's balance if it was previously "Pending".
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Update Status</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
