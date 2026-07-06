<?php
// Renders any flash messages set via FlashMessageTrait::setFlash()
if (!empty($_SESSION['flash'])):
    foreach ($_SESSION['flash'] as $type => $messages):
        foreach ($messages as $message):
            $cssClass = $type === 'success' ? 'alert alert-success'
                : ($type === 'error' ? 'alert alert-danger' : 'alert alert-info');
            ?>
            <div class="<?= $cssClass ?>"><?= htmlspecialchars($message) ?></div>
            <?php
        endforeach;
    endforeach;
    unset($_SESSION['flash']); // clear after displaying
endif;
?>