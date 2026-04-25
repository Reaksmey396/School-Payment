<?php
$mailConfig = require __DIR__ . '/../Components/mail_config.php';
$adminEmail = $mailConfig['admin_email'] ?? 'khimreaksmey123@gmail.com';
$smtpEmail = $mailConfig['smtp_email'] ?? $adminEmail;
$smtpAppPassword = preg_replace('/\s+/', '', $mailConfig['smtp_app_password'] ?? '');
$formStatus = '';
$formMessage = '';

if (($_GET['sent'] ?? '') === '1') {
    $formStatus = 'success';
    $formMessage = 'Thank you. Your message was sent to our admin email.';
} elseif (($_GET['saved'] ?? '') === '1') {
    $formStatus = 'success';
    $formMessage = 'Thank you. Your message was saved for our admin team to review.';
}

function smtpRead($socket)
{
    $response = '';

    while ($line = fgets($socket, 515)) {
        $response .= $line;

        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    return $response;
}

function smtpCommand($socket, $command, $expectedCodes)
{
    if ($command !== null) {
        fwrite($socket, $command . "\r\n");
    }

    $response = smtpRead($socket);
    $code = (int) substr($response, 0, 3);

    return in_array($code, (array) $expectedCodes, true)
        ? [true, $response]
        : [false, $response];
}

function sendContactEmail($to, $fromEmail, $fromPassword, $replyName, $replyEmail, $subject, $body)
{
    if ($fromPassword === '') {
        return [false, 'Please add the Gmail App Password for ' . $fromEmail . ' in $smtpAppPassword at the top of Contact.php.'];
    }

    if (strlen($fromPassword) !== 16) {
        return [false, 'The Gmail App Password must be 16 characters. Create a Gmail App Password for ' . $fromEmail . ', paste it into Components/mail_config.php, then restart Apache.'];
    }

    if (!extension_loaded('openssl')) {
        return [false, 'PHP OpenSSL is not enabled, so Gmail SMTP cannot start a secure connection. Enable openssl in php.ini and restart Apache.'];
    }

    $socket = stream_socket_client('tcp://smtp.gmail.com:587', $errorNumber, $errorMessage, 30);

    if (!$socket) {
        return [false, 'SMTP connection failed: ' . $errorMessage];
    }

    stream_set_timeout($socket, 30);

    [$ok, $response] = smtpCommand($socket, null, 220);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP greeting failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, 'EHLO localhost', 250);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP EHLO failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, 'STARTTLS', 220);
    if (!$ok || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return [false, 'SMTP TLS failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, 'EHLO localhost', 250);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP secure EHLO failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, 'AUTH LOGIN', 334);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP auth start failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, base64_encode($fromEmail), 334);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP username failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, base64_encode($fromPassword), 235);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP password failed. Use a Gmail App Password, not your normal Gmail password.'];
    }

    [$ok, $response] = smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', 250);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP sender failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP recipient failed: ' . trim($response)];
    }

    [$ok, $response] = smtpCommand($socket, 'DATA', 354);
    if (!$ok) {
        fclose($socket);
        return [false, 'SMTP data failed: ' . trim($response)];
    }

    $safeReplyName = str_replace(['"', "\r", "\n"], ['', ' ', ' '], $replyName);
    $safeSubject = str_replace(["\r", "\n"], ' ', $subject);
    $message = [
        'Date: ' . date(DATE_RFC2822),
        'To: Admin <' . $to . '>',
        'From: RUPP-PAY Contact <' . $fromEmail . '>',
        'Reply-To: "' . $safeReplyName . '" <' . $replyEmail . '>',
        'Subject: ' . $safeSubject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        '',
        str_replace("\n.", "\n..", str_replace(["\r\n", "\r"], "\n", $body)),
    ];

    fwrite($socket, implode("\r\n", $message) . "\r\n.\r\n");
    $response = smtpRead($socket);
    $code = (int) substr($response, 0, 3);

    smtpCommand($socket, 'QUIT', 221);
    fclose($socket);

    return $code === 250
        ? [true, 'Email sent successfully.']
        : [false, 'SMTP send failed: ' . trim($response)];
}

function saveContactMessage($firstName, $lastName, $email, $subject, $message, $emailStatus)
{
    $storageDir = __DIR__ . '/../storage';
    $storageFile = $storageDir . '/contact_messages.csv';

    if (!is_dir($storageDir) && !mkdir($storageDir, 0777, true)) {
        return false;
    }

    $isNewFile = !file_exists($storageFile);
    $file = fopen($storageFile, 'ab');

    if (!$file) {
        return false;
    }

    if ($isNewFile) {
        fputcsv($file, ['Date', 'First Name', 'Last Name', 'Email', 'Subject', 'Message', 'Email Status']);
    }

    fputcsv($file, [date('Y-m-d H:i:s'), $firstName, $lastName, $email, $subject, $message, $emailStatus]);
    fclose($file);

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $studentEmail = trim($_POST['studentEmail'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $subjectLabels = [
        'payment' => 'Payment Issue',
        'account' => 'Account Access',
        'other' => 'Other Inquiry',
    ];

    if ($firstName === '' || $lastName === '' || $studentEmail === '' || $subject === '' || $message === '') {
        $formStatus = 'error';
        $formMessage = 'Please complete every field before submitting your inquiry.';
    } elseif (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
        $formStatus = 'error';
        $formMessage = 'Please enter a valid student email address.';
    } else {
        $headerName = preg_replace('/[\r\n]+/', ' ', $firstName . ' ' . $lastName);
        $headerEmail = preg_replace('/[\r\n]+/', '', $studentEmail);
        $safeSubject = $subjectLabels[$subject] ?? 'Other Inquiry';
        $mailSubject = 'New RUPP-PAY Contact Inquiry: ' . $safeSubject;
        $mailBody = "A new message was submitted from the RUPP-PAY contact form.\n\n";
        $mailBody .= "Name: {$firstName} {$lastName}\n";
        $mailBody .= "Student Email: {$studentEmail}\n";
        $mailBody .= "Inquiry Subject: {$safeSubject}\n\n";
        $mailBody .= "Message:\n{$message}\n";

        $sent = false;
        $sendError = 'Gmail SMTP is not configured.';

        if ($smtpAppPassword !== '') {
            [$sent, $sendError] = sendContactEmail($adminEmail, $smtpEmail, $smtpAppPassword, $headerName, $headerEmail, $mailSubject, $mailBody);
        }

        if ($sent) {
            saveContactMessage($firstName, $lastName, $studentEmail, $safeSubject, $message, 'Sent to ' . $adminEmail);
            header('Location: Contact.php?sent=1');
            exit;
        } elseif (saveContactMessage($firstName, $lastName, $studentEmail, $safeSubject, $message, 'Saved only: ' . $sendError)) {
            header('Location: Contact.php?saved=1');
            exit;
        } else {
            $formStatus = 'error';
            $formMessage = 'Your message could not be sent or saved. Please contact admin directly at ' . $adminEmail . '.';
        }
    }
}

include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<style>
  /* ១. ទាញយក Font "Inter" ដែលជា Font លេខ ១ សម្រាប់ UI/UX Dashboard */
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

  /* ២. កំណត់ទៅលើ Body ឱ្យមានសោភ័ណភាពខ្ពស់ */
  body {
    font-family: 'Inter', sans-serif !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    color: #1e293b; /* ពណ៌ Slate-800 ជួយឱ្យអក្សរមើលទៅទន់ភ្នែកជាងពណ៌ខ្មៅសុទ្ធ */
  }

  /* ៣. ការកំណត់ចំណងជើង (Headings) ឱ្យមើលទៅ "Premium" */
  h1, h2, h3, .font-bold {
    font-family: 'Inter', sans-serif;
    font-weight: 700 !important;
    letter-spacing: -0.04em !important; /* គាបអក្សរឱ្យជិតគ្នា បង្កើតអារម្មណ៍ទំនើប */
    line-height: 1.1;
  }

  /* ៤. ការកំណត់អត្ថបទធម្មតា (Paragraph) */
  p {
    line-height: 1.6;
    letter-spacing: -0.01em;
    color: #475569; /* ពណ៌ Slate-600 */
  }

  /* ៥. កែសម្រួល Font ក្នុង Form និង Button ឱ្យត្រូវគ្នា */
  input, button, select, textarea {
    font-family: 'Inter', sans-serif !important;
    letter-spacing: -0.01em;
  }
</style>

<section class="relative h-[600px] flex items-center justify-center text-center overflow-hidden">
  <img class="absolute inset-0 w-full h-full object-cover -z-20"
  src="https://airial.travel/_next/image?url=https%3A%2F%2Fcoinventmediastorage.blob.core.windows.net%2Fmedia-storage-container%2Fgphoto_ChIJaX0H5J9RCTERYEZDLoKOEyA_0.jpg&w=3840&q=75">

  <div class="absolute inset-0 bg-black/50 -z-10"></div>

  <div class="absolute top-10 left-10 z-20">
    <button onclick="back()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-xl top-15 relative right-5 fw-bold shadow-lg group">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back
    </button>
  </div>

  <div class="px-6 animate-fadeUp">
    <h1 class="text-5xl md:text-6xl font-black uppercase text-white mb-6">
      Contact <span class="text-blue-500">Us</span>
    </h1>

    <p class="text-gray-200 max-w-xl mx-auto text-lg">
      If you have any questions about the School Fee Payment System,
      feel free to contact our support team. We are always ready to help you.
    </p>
  </div>
</section>

<div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-7xl mx-auto w-full">
        
        <div class="flex flex-col space-y-5">
            <div class="text-left">
                <h1 class="text-4xl font-extrabold text-slate-950 mb-4 tracking-tight">Contact Information</h1>
                <p class="text-slate-600 text-lg leading-relaxed max-w-2xl">
                    Visit our administrative office or reach out via phone or email. We typically respond to queries within 24 business hours.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-8 rounded-3xl shadow-[0_20px_60px_-10px_rgba(0,0,0,0.03)] border border-slate-100 flex items-start gap-6 transition-all hover:shadow-[0_20px_60px_-10px_rgba(0,0,0,0.05)]">
                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-950 mb-1.5">Our Location</h4>
                        <p class="text-slate-600 text-[15px] leading-relaxed">
                            123 Education Excellence Way, Academic District, New York, NY 10001
                        </p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-[0_20px_60px_-10px_rgba(0,0,0,0.03)] border border-slate-100 flex items-start gap-6 transition-all hover:shadow-[0_20px_60px_-10px_rgba(0,0,0,0.05)]">
                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-950 mb-1.5">Phone Support</h4>
                        <p class="text-slate-700 text-[15px] leading-relaxed font-medium">+1 (555) 123-4567</p>
                        <p class="text-slate-500 text-sm mt-0.5">Mon-Fri, 8:00 AM - 4:00 PM</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-[0_20px_60px_-10px_rgba(0,0,0,0.03)] border border-slate-100 flex items-start gap-6 transition-all hover:shadow-[0_20px_60px_-10px_rgba(0,0,0,0.05)]">
                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-950 mb-1.5">Email Address</h4>
                        <div class="space-y-1 text-slate-700 text-[15px] leading-relaxed">
                            <a href="mailto:support@gmail.com" class="block font-medium hover:text-blue-600 hover:underline transition">support@edupayportal.com</a>
                            <a href="mailto:billing@edupayportal.com" class="block font-medium hover:text-blue-600 hover:underline transition">billing@edupayportal.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-12 rounded-3xl shadow-[0_20px_60px_-10px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col space-y-5 lg:sticky lg:top-12 h-fit">
            
            <div class="text-left">
                <h1 class="text-3xl font-extrabold text-slate-950 mb-3 tracking-tight">Send us a Message</h1>
                <p class="text-slate-600 text-[15px] leading-relaxed max-w-xl">
                    Fill out the form below and our finance department will get back to you.
                </p>
            </div>

            <?php if ($formMessage !== ''): ?>
                <div class="<?php echo $formStatus === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?> border rounded-xl px-4 py-3 text-sm font-semibold">
                    <?php echo htmlspecialchars($formMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form id="contactForm" method="POST" action="Contact.php" autocomplete="off" class="grid grid-cols-2 gap-x-6 gap-y-7">
                <div class="col-span-1">
                    <label for="firstName" class="block text-slate-700 font-semibold mb-2.5 text-sm">First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="John" required autocomplete="off"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 placeholder:text-slate-300 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-[15px]">
                </div>

                <div class="col-span-1">
                    <label for="lastName" class="block text-slate-700 font-semibold mb-2.5 text-sm">Last Name</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Doe" required autocomplete="off"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 placeholder:text-slate-300 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-[15px]">
                </div>

                <div class="col-span-2">
                    <label for="studentEmail" class="block text-slate-700 font-semibold mb-2.5 text-sm">Your Email Address</label>
                    <input type="email" id="studentEmail" name="studentEmail" placeholder="john.doe@school.edu" required autocomplete="off"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 placeholder:text-slate-300 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-[15px]">
                </div>

                <div class="col-span-2">
                    <label for="subject" class="block text-slate-700 font-semibold mb-2.5 text-sm">Inquiry Subject</label>
                    <div class="relative">
                        <select id="subject" name="subject" required
                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 placeholder:text-slate-300 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-[15px] appearance-none cursor-pointer">
                            <option value="payment" <?php echo ($_POST['subject'] ?? '') === 'payment' ? 'selected' : ''; ?>>Payment Issue</option>
                            <option value="account" <?php echo ($_POST['subject'] ?? '') === 'account' ? 'selected' : ''; ?>>Account Access</option>
                            <option value="other" <?php echo ($_POST['subject'] ?? '') === 'other' ? 'selected' : ''; ?>>Other Inquiry</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <label for="message" class="block text-slate-700 font-semibold mb-2.5 text-sm">Your Message</label>
                    <textarea id="message" name="message" rows="5" placeholder="Please describe your issue in detail..." required autocomplete="off"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 placeholder:text-slate-300 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all text-[15px] resize-none"></textarea>
                </div>

                <div class="col-span-2 pt-3">
                    <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 rounded-xl text-base hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 active:scale-[0.98]">
                        Submit Inquiry
                    </button>
                </div>
        </form>
        </div>
    </div>
</div>

<script>
  function back(){
    window.history.back()
  }

  function clearContactForm() {
    const form = document.getElementById('contactForm');

    if (form) {
      form.reset();
    }
  }

  document.addEventListener('DOMContentLoaded', clearContactForm);
  window.addEventListener('pageshow', clearContactForm);
</script>

<?php 
include '../Components/Footer.php';
?>
