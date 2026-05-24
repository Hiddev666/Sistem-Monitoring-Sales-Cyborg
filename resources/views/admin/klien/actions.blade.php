<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.klien.edit', $klien) }}" class="btn btn-sm btn-warning" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    <form action="{{ route('admin.klien.destroy', $klien) }}" method="POST" class="d-inline" 
          onsubmit="return confirm('Yakin ingin menghapus klien ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
