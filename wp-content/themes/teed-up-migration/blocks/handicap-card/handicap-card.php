<?php
/**
 * Handicap Card Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$handicap = get_field('current_handicap') ?: 'N/A';
$trend = get_field('handicap_trend') ?: 'stable';
?>

<div class="handicap-card-block">
    <div class="handicap-value">
        <span class="label">Current Handicap</span>
        <span class="value">
            <?php echo esc_html($handicap); ?>
        </span>
    </div>
    <?php if ($trend): ?>
    <div class="handicap-trend <?php echo esc_attr($trend); ?>">
        <?php echo esc_html(ucfirst($trend)); ?>
    </div>
    <?php
endif; ?>
</div>

<style>
    .handicap-card-block {
        background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
        color: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        max-width: 300px;
        font-family: 'Inter', sans-serif;
    }

    .handicap-value .label {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        opacity: 0.7;
        margin-bottom: 4px;
    }

    .handicap-value .value {
        font-size: 48px;
        font-weight: 800;
    }

    .handicap-trend {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin-top: 12px;
    }

    .handicap-trend.improving {
        background: #2ecc71;
    }

    .handicap-trend.stable {
        background: #95a5a6;
    }
</style>