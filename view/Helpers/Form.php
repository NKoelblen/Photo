<?php
namespace App\Helpers;

use App\Entity\AbstractEntity;
use DateTimeInterface;

class Form
{
    private AbstractEntity $entity;
    private array $errors;
    public function __construct(AbstractEntity $entity, array $errors)
    {
        $this->entity = $entity;
        $this->errors = $errors;
    }
    public function input(string $type, string $field, string $label, array $attributes = [], array $classes = []): string
    {
        $method = "get_$field";
        $value = method_exists($this->entity, $method) ? $this->entity->$method() : '';
        if ($value instanceof DateTimeInterface):
            $value = $value->format('Y-m-d\TH:i');
        endif;

        ob_start(); ?>
        <div class="mb-3 input-group">
            <?php if ($label): ?>
                <label class="input-group-text" for="<?= $field; ?>"><?= $label; ?></label>
            <?php endif; ?>
            <input class="<?= $this->get_classes($field, $classes); ?>" type="<?= $type; ?>" name="<?= $field; ?>"
                id="<?= $field; ?>" <?= $type === 'file' ? '' : 'value="' . $value . '"'; ?><?= implode(' ', $attributes); ?>>
            <?= $this->get_invalid_feedback($field); ?>
        </div>
        <?php return ob_get_clean();
    }
    private function get_classes(string $field, array $classes): string
    {
        $class = 'form-control';
        if (isset($this->errors[$field])):
            $class .= ' is-invalid';
        endif;
        if (!empty($classes)):
            $class .= ' ' . implode(' ', $classes);
        endif;
        return $class;
    }

    private function get_invalid_feedback(string $field): string
    {
        if (isset($this->errors[$field])):
            if (is_array($this->errors[$field])):
                $error = implode('<br>', $this->errors[$field]);
            else:
                $error = $this->errors[$field];
            endif;
            return '<div class="invalid-feedback">' . $error . '</div>';
        endif;
        return '';
    }
}