<?php
$_tStmt = $conn->prepare(
    "SELECT client_name, business_name, role_title, rating, headline,
            problem_before, solution_after, recommendation
     FROM testimonials
     WHERE status = 'approved'
     ORDER BY reviewed_at DESC
     LIMIT 12"
);
$_tStmt->execute();
$_tResult = $_tStmt->get_result();
$_testimonials = $_tResult->fetch_all(MYSQLI_ASSOC);
$_tStmt->close();
?>
<section id="testimonials" class="section-padding w-full"<?= empty($_testimonials) ? ' style="display:none"' : '' ?>>
    <div class="rc-container">
        <div class="text-center mb-12">
            <div class="rc-section-tag">Client Testimonials</div>
            <h2 class="font-bold text-5xl text-white">What Clients Say</h2>
            <p class="lead text-white/50">Real words from real projects.</p>
        </div>

        <div class="rc-testimonials-grid">
            <?php foreach ($_testimonials as $t): ?>
            <div class="rc-testimonial-card">
                <div class="rc-stars">
                    <?php
                    $r = (int)$t['rating'];
                    echo str_repeat('<span class="rc-star filled">&#9733;</span>', $r);
                    echo str_repeat('<span class="rc-star">&#9733;</span>', 5 - $r);
                    ?>
                </div>
                <?php if (!empty($t['headline'])): ?>
                <p class="rc-testimonial-headline">"<?= htmlspecialchars($t['headline'], ENT_QUOTES, 'UTF-8') ?>"</p>
                <?php else: ?>
                <p class="rc-testimonial-headline">"<?= htmlspecialchars(mb_substr($t['solution_after'], 0, 120), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>…"</p>
                <?php endif; ?>
                <p class="rc-testimonial-body"><?= htmlspecialchars($t['recommendation'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <div class="rc-testimonial-author">
                    <div class="rc-author-avatar"><?= htmlspecialchars(mb_substr($t['client_name'], 0, 1), ENT_QUOTES, 'UTF-8') ?></div>
                    <div>
                        <div class="rc-author-name"><?= htmlspecialchars($t['client_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="rc-author-role"><?= htmlspecialchars($t['role_title'], ENT_QUOTES, 'UTF-8') ?><?= !empty($t['business_name']) ? ', ' . htmlspecialchars($t['business_name'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
