<?php

declare(strict_types=1);

namespace G4\Api\Controller;

use G4\Api\App\Validator;

/** Contrôleur pour les associations utilisateur-tâche. */
class UserTask extends ControllerAbstract
{
    #[\Override]
    public function getIndexAction(): mixed
    {
        return $this->ok([]);
    }

    /** Alias POST → délègue à putAddTaskToUserAction(). */
    public function postAddTaskToUserAction(): mixed
    {
        return $this->putAddTaskToUserAction();
    }

    /** Associe une tâche existante à un utilisateur existant. */
    public function putAddTaskToUserAction(): mixed
    {
        $v = new Validator($this->getParams());
        $v->required('userId')->int()->end();
        $v->required('taskId')->int()->end();

        if ($v->fails()) {
            return $this->fail($v->firstError(), 400);
        }

        $this->requireUser($v->get('userId'));
        $this->requireTask($v->get('taskId'));

        $userTask = new \G4\Api\Model\UserTask($v->get('userId'));
        if ($userTask->addTask($v->get('taskId'))->save()) {
            return $this->ok(1);
        }

        return $this->fail('Unable to add Task to user', 500);
    }

    /**
     * Retire l'association entre un utilisateur et une tâche.
     * Idempotente : retourne 1 même si la tâche n'était pas dans la liste.
     */
    public function deleteUserTaskAction(): mixed
    {
        $v = new Validator($this->getParams());
        $v->required('userId')->int()->end();
        $v->required('taskId')->int()->end();

        if ($v->fails()) {
            return $this->fail($v->firstError(), 400);
        }

        $this->requireUser($v->get('userId'));

        $userTask = new \G4\Api\Model\UserTask($v->get('userId'));

        if ($userTask->hasTask($v->get('taskId'))) {
            if ($userTask->removeTask($v->get('taskId'))->save()) {
                return $this->ok(1);
            }

            return $this->fail('Unable to delete Task of user', 500);
        }

        return $this->ok(1);
    }
}
