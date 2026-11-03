<?php
/**
 * Student confirmation email.
 *
 * @var object $b        Booking row.
 * @var string $when     Local date and time.
 * @var string $zone     Time zone name.
 * @var int    $duration Minutes.
 * @var string $contact  Contact email.
 * @var string $whatsapp WhatsApp number with country code (may be empty).
 * @var string $duration_label Session length text, e.g. "15-20 minutes".
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html><body style="margin:0;padding:24px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#fff;border-radius:8px;padding:24px;">
<tr><td>
<h1 style="font-size:20px;margin:0 0 16px;">Your session is confirmed</h1>
<p>Hi <?php echo esc_html( $b->student_name ); ?>,</p>
<p>Your 1-to-1 French speaking session (<?php echo esc_html( (string) ( $duration_label ?? $duration . ' minutes' ) ); ?>) is booked.</p>
<p><strong><?php echo esc_html( $when ); ?></strong><br><?php echo esc_html( $zone ); ?></p>
<p style="margin:24px 0;"><a href="<?php echo esc_url( $b->meet_link ); ?>" style="background:#1a1a1a;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;display:inline-block;">Join the session</a></p>
<p style="font-size:14px;">When you open the link, click <em>Ask to join</em> and your trainer will let you in.</p>
<p style="font-size:14px;">Booking reference: <strong><?php echo esc_html( $b->ref ); ?></strong></p>
<?php $whatsapp = (string) ( $whatsapp ?? '' ); $wa_digits = preg_replace( '/\D/', '', $whatsapp ); ?>
<?php if ( '' !== $contact || '' !== $wa_digits ) : ?>
<p style="font-size:14px;">Questions? Contact
<?php if ( '' !== $contact ) : ?><a href="mailto:<?php echo esc_attr( $contact ); ?>"><?php echo esc_html( $contact ); ?></a><?php endif; ?>
<?php if ( '' !== $contact && '' !== $wa_digits ) : ?> or <?php endif; ?>
<?php if ( '' !== $wa_digits ) : ?>WhatsApp <a href="https://wa.me/<?php echo esc_attr( $wa_digits ); ?>"><?php echo esc_html( $whatsapp ); ?></a><?php endif; ?>.</p>
<?php endif; ?>
</td></tr></table>
</body></html>
