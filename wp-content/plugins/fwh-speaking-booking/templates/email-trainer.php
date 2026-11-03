<?php
/**
 * Trainer notification email (coordinator in CC).
 *
 * @var object $b    Booking row.
 * @var string $when Toronto date and time.
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html><body style="margin:0;padding:24px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#fff;border-radius:8px;padding:24px;">
<tr><td>
<h1 style="font-size:20px;margin:0 0 16px;">New speaking session booked</h1>
<p><strong><?php echo esc_html( $when ); ?></strong> (Toronto time)</p>
<p>Student: <?php echo esc_html( $b->student_name ); ?></p>
<p style="margin:24px 0;"><a href="<?php echo esc_url( $b->meet_link ); ?>" style="background:#1a1a1a;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;display:inline-block;">Open the Meet link</a></p>
<p style="font-size:14px;">Please join from your own Google account so you are the host and can admit the student.</p>
<p style="font-size:14px;">Booking reference: <strong><?php echo esc_html( $b->ref ); ?></strong></p>
</td></tr></table>
</body></html>
