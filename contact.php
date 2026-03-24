<?php
declare(strict_types=1);

header("Content-Type: text/html; charset=utf-8");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

function respond(int $code, string $title, string $message): void {
  http_response_code($code);
  $t = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
  $m = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
  echo "<!doctype html><html lang='en'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>"
    . "<title>{$t}</title>"
    . "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet' "
    . "integrity='sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH' crossorigin='anonymous'>"
    . "</head><body class='bg-light'>"
    . "<main class='container py-5'><div class='row justify-content-center'><div class='col-lg-7'>"
    . "<div class='card border-0 shadow-sm'><div class='card-body p-4'>"
    . "<h1 class='h4 mb-2'>{$t}</h1><p class='text-muted mb-4'>{$m}</p>"
    . "<a class='btn btn-primary' href='index.html#contact'>Back</a>"
    . "</div></div></div></div></main></body></html>";
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  respond(405, "Method not allowed", "Please submit the form from the website.");
}

// Basic anti-bot: require same-origin-ish navigation via Referer (best-effort, not a hard security boundary).
// You can remove this if it blocks legitimate deployments.
$ref = $_SERVER["HTTP_REFERER"] ?? "";
if ($ref !== "" && !preg_match("~^https?://~i", $ref)) {
  respond(400, "Invalid request", "Bad referer.");
}

$name = trim((string)($_POST["name"] ?? ""));
$email = trim((string)($_POST["email"] ?? ""));
$message = trim((string)($_POST["message"] ?? ""));

if ($name === "" || $email === "" || $message === "") {
  respond(400, "Missing fields", "Please fill in name, email, and message.");
}

if (mb_strlen($name) > 80 || mb_strlen($email) > 120 || mb_strlen($message) > 1200) {
  respond(400, "Too long", "Please shorten your input and try again.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  respond(400, "Invalid email", "Please enter a valid email address.");
}

// Load config (optional). If missing, don't break the site—just show a helpful message.
$configPath = __DIR__ . DIRECTORY_SEPARATOR . "config.php";
if (!is_file($configPath)) {
  respond(501, "Backend not configured", "To enable saving messages, copy config.php.example to config.php and import schema.sql into MySQL.");
}

/** @var array{db: array{host:string,user:string,pass:string,name:string,port:int,charset:string}} $cfg */
$cfg = require $configPath;
$db = $cfg["db"] ?? null;
if (!is_array($db)) {
  respond(500, "Server misconfigured", "Invalid database config.");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $mysqli = new mysqli(
    (string)($db["host"] ?? "127.0.0.1"),
    (string)($db["user"] ?? ""),
    (string)($db["pass"] ?? ""),
    (string)($db["name"] ?? ""),
    (int)($db["port"] ?? 3306)
  );
  $mysqli->set_charset((string)($db["charset"] ?? "utf8mb4"));

  $ip = $_SERVER["REMOTE_ADDR"] ?? null;
  $ua = $_SERVER["HTTP_USER_AGENT"] ?? null;

  // Store IP as binary where possible (supports IPv4/IPv6)
  $ipBin = null;
  if (is_string($ip) && $ip !== "") {
    $packed = @inet_pton($ip);
    if ($packed !== false) $ipBin = $packed;
  }

  $stmt = $mysqli->prepare("INSERT INTO contact_messages (name, email, message, ip, user_agent) VALUES (?, ?, ?, ?, ?)");
  // MySQLi bind_param doesn't accept null reliably for 's' in all environments, so coerce to empty string.
  $ipForDb = $ipBin === null ? "" : $ipBin;
  $uaForDb = $ua === null ? "" : $ua;
  $stmt->bind_param("sssss", $name, $email, $message, $ipForDb, $uaForDb);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
} catch (Throwable $e) {
  respond(500, "Server error", "We couldn't save your message right now. Please try again later.");
}

respond(200, "Message sent", "Thanks! Your message was received successfully.");

