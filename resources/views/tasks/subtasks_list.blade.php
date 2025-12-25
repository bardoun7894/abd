@foreach($subtasks as $subtask)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $subtask->title }}</td>
    <td>{{ $subtask->worker->worker_name }}</td>
    <td>
        <span class="badge bg-{{ $subtask->priority == 'عالي' ? 'danger' : ($subtask->priority == 'متوسط' ? 'warning' : 'info') }}">
            {{ $subtask->priority }}
        </span>
    </td>
    <td>
        <span class="badge bg-{{ $subtask->status == 'مكتمل' ? 'success' : 'primary' }}">
            {{ $subtask->status }}
        </span>
    </td>
    <td>{{ $subtask->due_date->format('Y-m-d') }}</td>
    <td>
        <div class="btn-group">
            <button class="btn btn-sm btn-warning" onclick="editSubtask({{ $subtask->id }})">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteSubtask({{ $subtask->id }})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>
@endforeach
