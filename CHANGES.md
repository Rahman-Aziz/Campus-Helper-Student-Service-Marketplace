# CampusHelper — Changes

Drop this `htdocs` folder into XAMPP (replacing your existing one), then in
phpMyAdmin run **`migration_payment_verification.sql`** once against the
`campus_helper` database. (The app also applies it automatically on first run,
the same way the existing code adds the `qr_code` column, but running it
manually is the reliable option.)

Make sure the web server can write to `uploads/qr/` and `uploads/receipts/`.

## 1. Sellers must upload a QR before listing
`pages/create_service.php`
- Reads the seller's `qr_code` from the database.
- No QR on file → the listing form is replaced with an "Add your payment QR
  first" prompt linking to the profile page.
- Any POST without a QR is blocked server-side and redirected to the profile,
  so a listing can never be created or saved without a payable QR behind it.

## 2. Buyer ↔ Seller chat
- `pages/chat.php` — a real threaded conversation (chat bubbles, auto-scroll,
  Enter to send, marks incoming messages read).
- `pages/messages.php` — inbox of all conversations with last-message preview
  and unread badges.
- `pages/service.php` — "Contact Me" now opens the chat with the listing
  attached.
- `pages/send_message.php` — now redirects to the inbox by default.
- "Messages" links added to the home and dashboard navs.
(The `messages` table already existed; this adds the missing interface.)

## 3. (Optional) Payment receipt verification before a receipt is issued
New flow after the buyer scans the QR and clicks OK:
1. A **pending** payment is created (`order.php`), order stays `pending`.
2. Buyer is sent to `pages/upload_proof.php` to upload their bank/e-wallet
   receipt (PDF preferred; JPG/PNG/WEBP also accepted, max 5 MB). The seller is
   notified via a chat message.
3. Seller reviews it on `pages/verify_payment.php` and either:
   - **Verifies** → payment marked paid, order → `in_progress`, service order
     count incremented, buyer notified, and the CampusHelper receipt becomes
     available; or
   - **Rejects** (with a reason) → buyer is asked to re-upload.
4. `pages/receipt.php` refuses to issue a receipt until `verify_status` is
   `verified`; before that it shows a clear pending/rejected page.

Supporting pieces:
- `includes/payments.php` — adds the `proof_file`, `verify_status`,
  `verified_at`, `reject_reason` columns (best-effort) and backfills existing
  paid records as `verified` so old receipts keep working.
- `migration_payment_verification.sql` — the same changes for manual setup.
- Dashboard: Purchases tab shows Upload / Re-upload / Awaiting / View Receipt
  per state; Sales tab has a new **Payment** column with a "Verify Payment"
  action.

Removed the stale duplicate copies (`fyp/...`, `index-old*.php`) from the tree.
