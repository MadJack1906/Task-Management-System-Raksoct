<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class TaskListComponent extends Component
{
    use WithPagination;

    public bool $is_create = false;

    public $form;

    public $name;

    public $description;

    public $due_date;

    public $status;

    public $task;

    public $is_form_modal_visible = false;

    public $filter_status;

    public $search;

    public $sortField = null;
    public $sortDirection = "DESC";

    public function render()
    {
        $user = Auth::user();
        $tasks = $user->tasks();

        if ($status = $this->filter_status) {
            $tasks = $tasks->where('status', (int) $status);
        }

        if ($search = $this->search) {
            $tasks = $tasks->where(function (Builder $query) use ($search) {
                $query->where('name', "LIKE", "%{$search}%")
                    ->orWhere('description', "LIKE", "%{$search}%");
            });
        }

        return view('livewire.task-list-component', [
            "tasks" => $tasks
                ->orderBy($this->sortField ?? "id", $this->sortDirection)
                ->paginate(5)
        ]);
    }

    public function sort($field)
    {
        $this->sortField = $field;
        $this->sortDirection = $this->sortDirection === "DESC" ? "ASC" : "DESC";
    }

    public function openPopup($isCreate, $id = null)
    {
        if ($isCreate) {
            $this->is_create = ! $this->is_create;

            $this->name = null;
            $this->description = null;
            $this->due_date = null;
            $this->status = null;
        } else {
            $this->is_create = false;
            $this->task = $task = Task::find($id);

            $this->name = $task->name;
            $this->description = $task->description;
            $this->due_date = $task->due_date;
            $this->status = $task->status;
        }

        $this->is_form_modal_visible = true;
    }

    public function closePopup()
    {
        $this->is_create = false;
        $this->is_form_modal_visible = false;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'description' => 'required',
            'due_date' => 'required|date',
            'status' => 'required',
        ]);

        // If the is_create is true it will process to create a task record, else it will update a selected task
        if ($this->is_create) {

            $task = Task::make([
                'name' => $this->name,
                'description' => $this->description,
                'due_date' => $this->due_date,
                'status' => $this->status,
            ]);

            $task = $task->user()->associate(Auth::user());
            $task->save();
        } else {
            if (Gate::check('update', [Task::class, $this->task])) {
                $this->task->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'due_date' => $this->due_date,
                    'status' => $this->status,
                ]);
            } else {
                session()->flash("message", "You don't have the permission to update this task");
            }
        }
        session()->flash("message", "Success");
        $this->closePopup();
    }

    public function delete($id)
    {
        $task = Task::find($id);

        if (Gate::check('delete', $task)) {
            $task->delete();
            session()->flash("message", "Success");
        } else {
            session()->flash("message", "You don't have the permission to delete this task");
        }

    }
}
