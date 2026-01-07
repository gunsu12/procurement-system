@extends('adminlte::page')

@section('title', 'Activity Logs')

@section('content_header')
<h1>Activity Logs</h1>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">System Activities</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>Subject</th>
                        <th>Changes</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr class="{{ $activity->log_name === 'server_error' ? 'table-danger' : ($activity->log_name === 'validation_error' ? 'table-warning' : '') }}">
                            <td>{{ $activity->id }}</td>
                            <td>
                                @if($activity->causer)
                                    {{ $activity->causer->name ?? $activity->causer->email }}
                                @else
                                    <span class="text-muted">System/Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($activity->log_name == 'server_error')
                                    <span class="badge badge-danger">ERROR</span>
                                @elseif($activity->log_name == 'validation_error')
                                    <span class="badge badge-warning">VALIDATION</span>
                                @endif
                                {{ $activity->description }}
                            </td>
                            <td>
                                @if($activity->subject)
                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($activity->properties && $activity->properties->count() > 0)
                                    <code style="display:block; max-width: 300px; max-height: 100px; overflow: auto;">
                                        {{ $activity->properties->toJson(JSON_PRETTY_PRINT) }}
                                    </code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No activity logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">
        {{ $activities->links() }}
    </div>
</div>
@stop

@section('css')
<style>
    /* Add any custom styles here */
</style>
@stop

@section('js')
<script>
    // Console log for debugging
    console.log('Activity Logs loaded');
</script>
@stop