@extends('backend.master.index')

@section('title', 'NOTIFICATIONS')

@section('breadcrumbs')
    <span>SETTINGS</span> / <span class="highlight">NOTIFICATIONS</span>
@endsection

@section('chart-js')
<script>
    $(function () {
        scion.centralized_button(false, false, false, false);
    });
</script>
@endsection

@section('content')
<div class="row notifications-page">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    All Notifications
                    <small class="text-muted">({{ $notifications->total() }} total)</small>
                </h5>
                <a href="{{ route('notifications.read_all') }}" class="btn btn-sm btn-outline-primary">Mark all as read</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Sender</th>
                                <th>Notification</th>
                                <th style="width: 160px;">Date</th>
                                <th style="width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                                @php
                                    $senderImage = optional($notification->sender)->profile_img ?: 'default.jpg';
                                @endphp
                                <tr class="{{ $notification->is_read ? '' : 'table-primary' }}">
                                    <td class="align-middle">
                                        <img src="/images/profile/{{ $senderImage }}" alt="sender" class="rounded-circle" width="32" height="32" onerror="this.onerror=null; this.src='/images/profile/default.jpg';" />
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('notifications.read', $notification->id) }}" class="text-dark">
                                            <strong>{{ $notification->display_title ?: 'Notification' }}</strong>
                                        </a>
                                        @if(!empty($notification->display_message))
                                            <div class="text-muted small">{{ $notification->display_message }}</div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-muted small">{{ optional($notification->created_at)->format('M d, Y h:i A') }}</td>
                                    <td class="align-middle">
                                        @if($notification->is_read)
                                            <span class="badge badge-secondary">Read</span>
                                        @else
                                            <span class="badge badge-primary">Unread</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No notifications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($notifications->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .notifications-page {
        height: 100%;
        overflow-y: auto;
        padding-bottom: 16px;
    }
    .notifications-page > .col-12 {
        padding-bottom: 16px;
    }
</style>
@endsection
