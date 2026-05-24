<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" 
          onsubmit="return confirm('Yakin ingin menghapus user ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
