<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Back to SMS Dashboard -->
    <div class="mb-6">
        <a href="<?= \App\Core\View::url('admin/sms') ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to SMS Dashboard
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Bulk SMS Section -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-6">Send SMS to All Users</h2>
        <form action="<?= \App\Core\View::url('admin/sms/sendAll') ?>" method="POST" id="sendAllForm">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? bin2hex(random_bytes(32))) ?>">
            <div class="mb-4">
                <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                <select name="source" id="source" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="users">Users Table</option>
                    <option value="orders">Orders Table</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="template_id" class="block text-sm font-medium text-gray-700">Template</label>
                <select name="template_id" id="template_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a template (optional)</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= htmlspecialchars($template['id']) ?>"><?= htmlspecialchars($template['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" id="message" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                <p class="text-sm text-gray-500 mt-1">Character count: <span id="charCount">0</span>/160</p>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Send to All</button>
            </div>
        </form>
    </div>

    <!-- Refill Reminders Section -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-6">Send Refill Reminders (28 Days)</h2>
        <form action="<?= \App\Core\View::url('admin/sms/sendRefillReminders') ?>" method="POST" id="refillRemindersForm">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? bin2hex(random_bytes(32))) ?>">
            <div class="mb-4">
                <label for="refill_template_id" class="block text-sm font-medium text-gray-700">Template</label>
                <select name="template_id" id="refill_template_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a template</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= htmlspecialchars($template['id']) ?>"><?= htmlspecialchars($template['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Send Refill Reminders</button>
            </div>
        </form>
    </div>

    <!-- Latest Product Marketing Section -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-6">Send Latest Product Marketing SMS</h2>
        <form action="<?= \App\Core\View::url('admin/sms/sendLatestProductMarketing') ?>" method="POST" id="marketingForm">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? bin2hex(random_bytes(32))) ?>">
            <div class="mb-4">
                <label for="marketing_template_id" class="block text-sm font-medium text-gray-700">Template</label>
                <select name="template_id" id="marketing_template_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a template</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= htmlspecialchars($template['id']) ?>"><?= htmlspecialchars($template['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Send Marketing SMS</button>
            </div>
        </form>
    </div>

    <!-- Recent SMS Logs -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-6">Recent SMS Logs</h2>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent At</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($log['phone_number']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars(substr($log['message'], 0, 50)) . (strlen($log['message']) > 50 ? '...' : '') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $log['status'] === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= htmlspecialchars($log['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($log['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messageTextarea = document.getElementById('message');
        const charCountSpan = document.getElementById('charCount');

        function updateCharCount() {
            const content = messageTextarea.value;
            charCountSpan.textContent = content.length;
            if (content.length > 160) {
                charCountSpan.classList.add('text-red-500');
            } else {
                charCountSpan.classList.remove('text-red-500');
            }
        }

        messageTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>