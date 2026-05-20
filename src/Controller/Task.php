<?php

declare(strict_types=1);

namespace G4\Api\Controller;

use G4\Api\App\Validator;
use G4\Api\Model\TaskStatus;

/** Contrôleur CRUD pour les tâches. */
class Task extends ControllerAbstract
{
    #[\Override]
    public function getIndexAction(): mixed
    {
        return $this->ok((new \G4\Api\Model\Task())->getAll());
    }

    /** Alias POST → délègue à putAddTaskAction(). */
    public function postAddTaskAction(): mixed
    {
        return $this->putAddTaskAction();
    }

    /** Crée une nouvelle tâche. Retourne 201 + la tâche créée. */
    public function putAddTaskAction(): mixed
    {
        $v = new Validator($this->getParams());
        $v->required('title')->string()->max(255)->end();
        $v->optional('description')->string()->max(1000)->end();
        $v->optional('status')->int()->in([1, 2, 3, 4, 5])->end();

        if ($v->fails()) {
            return $this->fail($v->firstError(), 400);
        }

        $task = new \G4\Api\Model\Task();
        $result = $task
            ->setTitle($v->get('title'))
            ->setDescription($v->get('description') ?? '')
            ->setStatus($v->get('status') ?? TaskStatus::Backlog->value)
            ->save();

        if ($result) {
            $this->setCode(201);
            return $this->ok($task->toArray());
        }

        return $this->fail('Unable to create new Task', 500);
    }

    /** Alias POST → délègue à putEditTaskAction(). */
    public function postEditTaskAction(): mixed
    {
        return $this->putEditTaskAction();
    }

    /** Mise à jour partielle d'une tâche. */
    public function putEditTaskAction(): mixed
    {
        $v = new Validator($this->getParams());
        $v->required('id')->int()->end();
        $v->optional('title')->string()->max(255)->end();
        $v->optional('description')->string()->max(1000)->end();
        $v->optional('status')->int()->in([1, 2, 3, 4, 5])->end();

        if ($v->fails()) {
            return $this->fail($v->firstError(), 400);
        }

        $task = $this->requireTask($v->get('id'));

        $saved = $task
            ->setTitle($v->get('title') ?? $task->getTitle())
            ->setDescription($v->get('description') ?? $task->getDescription())
            ->setStatus($v->get('status') ?? $task->getStatus())
            ->save();

        if ($saved) {
            return $this->ok(1);
        }

        return $this->fail('Unable to update Task', 500);
    }

    /** Supprime une tâche après avoir vérifié son existence. */
    public function deleteTaskAction(): mixed
    {
        $v = new Validator($this->getParams());
        $v->required('id')->int()->end();

        if ($v->fails()) {
            return $this->fail($v->firstError(), 400);
        }

        $this->requireTask($v->get('id'));

        $task = new \G4\Api\Model\Task($v->get('id'));
        if ($task->delete()) {
            return $this->ok(1);
        }

        return $this->fail('Unable to delete Task', 500);
    }
}
