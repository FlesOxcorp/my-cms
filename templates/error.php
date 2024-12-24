<?php
$title = 'Ошибка';
$header = 'Произошла ошибка';
ob_start();
?>

<div class="error-container text-center">
    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <i class="bi bi-exclamation-circle text-danger" style="font-size: 4rem;"></i>
            </div>
            
            <h2 class="card-title mb-4">Упс! Что-то пошло не так</h2>
            
            <?php if (isset($error)): ?>
                <p class="card-text text-danger mb-4">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php endif; ?>

            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Вернуться назад
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-house"></i> На главную
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.error-container {
    max-width: 600px;
    margin: 2rem auto;
}

.card {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 15px;
}

.card-body {
    padding: 3rem;
}

.bi-exclamation-circle {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}
</style>

<?php
$content = ob_get_clean();
include 'layout.php';
?>