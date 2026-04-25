<?php
session_start();

if (!isset($_SESSION['is_admin']) || !in_array((int) $_SESSION['is_admin'], [1, 3], true)) {
    header('location:../Components/Login.php');
    exit();
}

$messageFile = __DIR__ . '/../storage/contact_messages.csv';
$messages = [];

if (file_exists($messageFile) && ($file = fopen($messageFile, 'rb'))) {
    $headers = fgetcsv($file);

    while (($row = fgetcsv($file)) !== false) {
        if ($headers && count($headers) === count($row)) {
            $messages[] = array_combine($headers, $row);
        }
    }

    fclose($file);
    $messages = array_reverse($messages);
}

include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<section class="min-h-screen bg-slate-50 px-6 py-28">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-8">
            <div>
                <h1 class="text-4xl font-black text-slate-950">Contact Messages</h1>
                <p class="text-slate-600 mt-2">Messages submitted from the Contact Us form.</p>
            </div>

            <a href="../Pages/Dashboards.php" class="bg-blue-600 text-white no-underline px-5 py-3 rounded-xl font-bold hover:bg-blue-700">
                Back to Dashboard
            </a>
        </div>

        <?php if (empty($messages)): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-600">
                No contact messages yet.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto bg-white border border-slate-200 rounded-2xl shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Message</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($messages as $message): ?>
                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                    <?php echo htmlspecialchars($message['Date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="px-4 py-4 font-semibold text-slate-900">
                                    <?php echo htmlspecialchars(trim(($message['First Name'] ?? '') . ' ' . ($message['Last Name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="px-4 py-4 text-blue-700">
                                    <a href="mailto:<?php echo htmlspecialchars($message['Email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($message['Email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-slate-700">
                                    <?php echo htmlspecialchars($message['Subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="px-4 py-4 max-w-md text-slate-700 whitespace-pre-wrap">
                                    <?php echo htmlspecialchars($message['Message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    <?php echo htmlspecialchars($message['Email Status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
include '../Components/Footer.php';
?>
