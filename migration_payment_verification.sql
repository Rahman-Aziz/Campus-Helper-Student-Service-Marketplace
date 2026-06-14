-- =====================================================================
-- Payment verification migration
-- =====================================================================
-- Adds support for the "buyer uploads payment proof -> seller verifies
-- -> receipt is issued" flow. Run once (e.g. in phpMyAdmin) against the
-- campus_helper database. The application also applies these changes
-- automatically (best-effort) via includes/payments.php, so running this
-- file manually is optional.

ALTER TABLE `payments`
    ADD COLUMN IF NOT EXISTS `proof_file`    VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `verify_status` VARCHAR(20)  NOT NULL DEFAULT 'awaiting_proof',
    ADD COLUMN IF NOT EXISTS `verified_at`   DATETIME     DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `reject_reason` VARCHAR(255) DEFAULT NULL;

-- verify_status values used by the app:
--   awaiting_proof : payment created, buyer has not uploaded a slip yet
--   submitted      : buyer uploaded a slip, waiting on the seller
--   verified       : seller confirmed payment -> receipt is issued
--   rejected       : seller rejected the slip -> buyer can re-upload

-- Keep historical / seed payments working: anything already paid before
-- this feature existed is treated as verified so its receipt still shows.
UPDATE `payments`
SET `verify_status` = 'verified'
WHERE `verify_status` = 'awaiting_proof'
  AND `proof_file` IS NULL
  AND (`paid_at` IS NOT NULL OR `status` IN ('paid', 'success'));
