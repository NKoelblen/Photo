<?php
namespace App\HTML;

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
        $get_field = "get_$field";
        $value = method_exists($this->entity, $get_field) ? $this->entity->$get_field() : '';
        if ($value instanceof DateTimeInterface):
            $value = $value->format('Y-m-d\TH:i');
        endif;

        ob_start(); ?>
        <div class="mb-3 input-group">
            <?php if ($label): ?>
                <label class="input-group-text" for="<?= $field; ?>"><?= $label; ?></label>
            <?php endif; ?>
            <input class="<?= $this->get_classes($field, $classes); ?>" type="<?= $type; ?>" name="<?= $field; ?>"
                id="<?= $field; ?>" <?= $type === 'file' ? '' : ($value ? 'value="' . $value . '"' : ''); ?>         <?= implode(' ', $attributes); ?>>
            <?= $this->get_invalid_feedback($field); ?>
        </div>
        <?php return ob_get_clean();
    }

    public function textarea(string $field, string $label, array $attributes = [], array $classes = []): string
    {
        $get_field = "get_$field";

        ob_start(); ?>
        <div class="mb-3 input-group">
            <label class="input-group-text" for="<?= $field; ?>"><?= $label; ?></label>
            <textarea class="<?= $this->get_classes($field, $classes); ?>" name="<?= $field; ?>" id="<?= $field; ?>"
                <?= implode(' ', $attributes); ?>><?= str_replace("<br />", "", $this->entity->$get_field()); ?></textarea>
            <?= $this->get_invalid_feedback($field); ?>
        </div>
        <?php return ob_get_clean();
    }

    public function select(string $field, string $label, array $options, bool $empty = false, array $attributes = [], array $classes = []): string
    {
        $get_field = "get_$field";

        ob_start(); ?>
        <div class="mb-3 input-group">
            <label class="input-group-text" for="<?= $field; ?>"><?= $label; ?></label>
            <select name="<?= $field; ?><?= in_array('multiple', $attributes, true) ? '[]' : ''; ?>" id="<?= $field; ?>"
                <?= in_array('multiple', $attributes, true) ? 'size="5"' : ''; ?>
                class="<?= $this->get_classes($field, $classes); ?>" <?= implode(' ', $attributes); ?>>
                <?php if ($empty): ?>
                    <option value=""></option>
                <?php endif; ?>
                <?php foreach ($options as $key => $data):
                    if (is_array($this->entity->$get_field())):
                        $selected = in_array($key, $this->entity->$get_field()) ? 'selected' : '';
                    else:
                        $selected = $key === $this->entity->$get_field() ? 'selected' : '';
                    endif; ?>
                    <option value="<?= $key; ?>" <?= $selected; ?>>
                        <?= is_array($data) ? $data['label'] : $data; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $this->get_invalid_feedback($field); ?>
        </div>
        <?php return ob_get_clean();
    }

    public function radio(string $field, string $label, array $options, array $attributes = [], array $classes = []): string
    {
        $get_field = "get_$field";

        ob_start(); ?>
        <fieldset class="mb-3">
            <legend><?= $label; ?></legend>
            <div class="container overflow-auto max-vh-50">
                <?php foreach ($options as $key => $data):
                    $checked = $key === $this->entity->$get_field() ? 'checked' : ''; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="<?= $field; ?>" id="<?= $field . $key; ?>"
                            value="<?= $key; ?>" <?= $checked; ?>             <?= implode(' ', $attributes); ?>>
                        <label class="form-check-label" for="<?= $field . $key; ?>" class="form-label">
                            <?= $data; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <?= $this->get_invalid_feedback($field); ?>
        <?php return ob_get_clean();
    }

    public function checkbox(string $field, string $label, array $options, array $attributes = [], array $classes = []): string
    {
        $method = "get_$field";

        ob_start(); ?>
        <fieldset class="mb-3">
            <legend><?= $label; ?></legend>
            <div class="container overflow-auto" style="max-height: 50vh;">
                <?php foreach ($options as $key => $value):
                    if (is_array($this->entity->$method())):
                        $checked = in_array($key, $this->entity->$method()) ? 'checked' : '';
                    else:
                        $checked = $key == $this->entity->$method() ? 'checked' : '';
                    endif; ?>
                    <div class="form-check <?= implode(' ', $classes); ?>">
                        <input class="form-check-input" type="checkbox" name="<?= $field; ?><?= count($options) > 1 ? '[]' : ''; ?>"
                            id="<?= $field . $key; ?>" value="<?= $key; ?>" <?= $checked; ?>             <?= implode(' ', $attributes); ?>>
                        <label class="form-check-label" for="<?= $field . $key; ?>" class="form-label"><?= $value; ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <?= $this->get_invalid_feedback($field); ?>
        <?php return ob_get_clean();
    }

    public function recursive_radio(string $field, string $label, array $options, array $attributes = [], array $classes = []): string
    {
        $get_field = "get_$field";

        ob_start(); ?>
        <fieldset class="mb-3">
            <legend><?= $label; ?></legend>
            <div class="container overflow-auto max-vh-50">
                <?php foreach ($options as $key => $data):
                    if (is_array($this->entity->$get_field())):
                        if (!empty($this->entity->$get_field()) && is_object($this->entity->$get_field()[0])):
                            $item_ids = [];
                            foreach ($this->entity->$get_field() as $item):
                                $item_ids[] = $item->get_id();
                            endforeach;
                            $key_datas = json_decode($key, true);
                            $key_ids = [];
                            foreach ($key_datas as $key_data):
                                $key_ids[] = $key_data['id'];
                            endforeach;
                            foreach ($key_ids as $key_id):
                                $checked = in_array($key_id, $item_ids) ? 'checked' : '';
                            endforeach;
                        else:
                            $key_ids = json_decode($key, true);
                            foreach ($key_ids as $key_id):
                                $checked = in_array($key_id, $this->entity->$get_field()) ? 'checked' : '';
                            endforeach;
                        endif;
                    else:
                        $checked = $key === $this->entity->$get_field() ? 'checked' : '';
                    endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="<?= $field; ?>" id="<?= $field . htmlentities($key); ?>"
                            value="<?= htmlentities($key); ?>" <?= $checked; ?>             <?= implode(' ', $attributes); ?>>
                        <label class="form-check-label" for="<?= $field . htmlentities($key); ?>" class="form-label">
                            <?= $data['label']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <?= $this->get_invalid_feedback($field); ?>
        <?php return ob_get_clean();
    }

    public function recursive_checkbox(string $field, string $label, array $options, array $attributes = [], array $classes = []): string
    {
        $get_field = "get_$field";

        ob_start(); ?>
        <fieldset class="mb-3">
            <legend><?= $label; ?></legend>
            <div class="container overflow-auto max-vh-50">
                <?php foreach ($options as $key => $data):
                    if (is_array($this->entity->$get_field())):
                        if (!empty($this->entity->$get_field()) && is_object($this->entity->$get_field()[0])):
                            $item_ids = [];
                            foreach($this->entity->$get_field() as $item):
                                $item_ids[] = $item->get_id();
                            endforeach;
                            $key_datas = json_decode($key, true);
                            $key_ids = [];
                            foreach ($key_datas as $key_data):
                                $key_ids[] = $key_data['id'];
                            endforeach;
                            foreach($key_ids as $key_id):
                                $checked = in_array($key_id, $item_ids) ? 'checked' : '';
                            endforeach;
                        else:
                            $key_ids = json_decode($key, true);
                            if (is_array($key_ids)):
                                foreach ($key_ids as $key_id):
                                    $checked = in_array($key_id, $this->entity->$get_field()) ? 'checked' : '';
                                endforeach;
                            else:
                                $checked = in_array($key_ids, $this->entity->$get_field()) ? 'checked' : '';
                            endif;
                        endif;
                    else:
                        $checked = $key === $this->entity->$get_field() ? 'checked' : '';
                    endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="<?= $field; ?>[]"
                            id="<?= $field . htmlentities($key); ?>" value="<?= htmlentities($key); ?>" <?= $checked; ?>             <?= implode(' ', $attributes); ?>>
                        <label class="form-check-label" for="<?= $field . htmlentities($key); ?>" class="form-label">
                            <?= $data['label'] . ($data['private'] === 1 ? ' <i class="bi bi-lock-fill" style="color: #dc3545"></i>' : ''); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <?= $this->get_invalid_feedback($field); ?>
        <?php return ob_get_clean();
    }

    public function parent_radio(string $field, string $label, array $options, array $attributes = [], array $classes = []): string
    {
        $get_field = "get_$field";
        ob_start(); ?>
        <fieldset class="mb-3">
            <legend><?= $label; ?></legend>
            <div class="container overflow-auto max-vh-50">
                <?php foreach ($options as $key => $data):
                    $key_datas = json_decode($key, true);
                    $data_id = $key_datas['id'];
                    $checked = '';
                    if($this->entity->$get_field()):
                        $checked = $key_datas['id'] === $this->entity->$get_field()->get_id() ? 'checked' : '';
                    endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="<?= $field; ?>" id="<?= $field . htmlentities($key); ?>"
                            value="<?= htmlentities($key); ?>" <?= $checked; ?> data-parent="<?= $data['parent_id']; ?>"
                            data-id="<?= $data_id; ?>"
                            <?= $data_id === $this->entity->get_id() ? 'disabled hidden' : ''; ?>>
                        <label class="form-check-label" for="<?= $field . htmlentities($key); ?>" class="form-label">
                            <?= $data['label']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <?= $this->get_invalid_feedback($field); ?>
        <?php return ob_get_clean();
    }

    public function children_checkbox(string $field, string $label, array $options, array $attributes = [], array $classes = []): string
    {
        $get_field = "get_$field";

        ob_start(); ?>
        <fieldset class="mb-3">
            <legend><?= $label; ?></legend>
            <div class="container overflow-auto max-vh-50">
                <?php foreach ($options as $key => $data):
                    $checked = '';
                    $key_datas = json_decode($key, true);
                    $id = $key_datas['id'];
                    if($this->entity->$get_field()):
                        $item_ids = [];
                        foreach ($this->entity->$get_field() as $item):
                            $item_ids[] = $item->get_id();
                        endforeach;
                        $checked = in_array($id, $item_ids) ? 'checked' : '';
                    endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="<?= $field; ?>[]" id="<?= $field . htmlentities($key); ?>"
                            value="<?= htmlentities($key); ?>" <?= $checked; ?> data-parent="<?= $data['parent_id']; ?>" data-id="<?= $id; ?>"
                            <?= $id === $this->entity->get_id() ? 'disabled hidden' : ''; ?>>
                        <label class="form-check-label" for="<?= $field . htmlentities($key); ?>" class="form-label">
                            <?= $data['label']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <?= $this->get_invalid_feedback($field); ?>
        <?php return ob_get_clean();
    }

    public function map(string $field, string $label, array $attributes = [], array $classes = [])
    {
        $get_field = "get_$field";

        ob_start(); ?>
        <fieldset class="mb-3 w-100">
            <legend><?= $label; ?></legend>
            <div id="map"></div>
            <div class="mb-3 input-group">
                <label class="input-group-text" for="<?= $field; ?>">Coordonnées : </label>
                <input class="<?= $this->get_classes($field, $classes); ?> form-control-plaintext px-3" type="text"
                    name="<?= $field; ?>" id="<?= $field; ?>" value="<?= $this->entity->$get_field(); ?>" <?= implode(' ', $attributes); ?>>
            </div>
            <?= $this->get_invalid_feedback($field); ?>
        </fieldset>
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