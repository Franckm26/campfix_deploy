<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Concern;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketBatchController extends Controller
{
    public function __invoke(Request $request, string $resource, string $action): JsonResponse
    {
        abort_unless(in_array($resource, ['concerns', 'reports'], true)
            && in_array($action, ['archive', 'restore', 'soft-delete', 'restore-deleted', 'permanent-delete'], true), 404);
        $user = $request->user();
        if ($resource === 'reports') {
            abort_unless($user->isSystemAdministrator() || in_array($user->role, ['mis', 'building_admin', 'school_admin', 'admin'], true), 403);
        }
        $request->validate([
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'required|integer|distinct|exists:'.$resource.',id',
        ]);
        $count = 0;
        $errors = [];
        foreach ($request->input('ids') as $id) {
            try {
                DB::transaction(function () use ($request, $resource, $action, $id, $user) {
                    $ticket = $resource === 'reports'
                        ? Report::forOperationalRole($user)->find($id)
                        : Concern::forUser($user->id)->find($id);
                    if (!$ticket) {
                        throw ValidationException::withMessages(['ids' => 'You cannot change one or more selected tickets.']);
                    }
                    $deletedAction = in_array($action, ['restore-deleted', 'permanent-delete'], true);
                    if ((bool) $ticket->is_deleted !== $deletedAction) {
                        throw ValidationException::withMessages(['ids' => 'One or more selected tickets are no longer in this list. Refresh and try again.']);
                    }
                    if (in_array($action, ['archive', 'restore'], true)) {
                        $archived = $ticket->isArchivedByUser($user->id);
                        if ($archived === ($action === 'archive')) {
                            throw ValidationException::withMessages(['ids' => 'One or more tickets are already in the requested state.']);
                        }
                        if ($action === 'archive') {
                            $ticket->archivedByUsers()->syncWithoutDetaching([$user->id => [
                                'archived_at' => now(), 'archive_folder_name' => 'My Archive',
                            ]]);
                        } else {
                            $ticket->archivedByUsers()->detach($user->id);
                        }
                        $column = $user->role.'_archived';
                        if (in_array($column, $ticket->getFillable(), true) && ($resource === 'reports' || $ticket->user_id === $user->id)) {
                            $ticket->update([$column => $action === 'archive']);
                        }
                        $type = $resource === 'reports' ? 'report' : 'concern';
                        ActivityLog::log($type.($action === 'archive' ? '_archived' : '_restored'), 'Batch '.$action.': '.$ticket->title, $ticket->id, $type);
                        return;
                    }
                    if ($action === 'soft-delete') {
                        if ($resource === 'reports') {
                            if ($ticket->assigned_to && $ticket->status !== 'Resolved') {
                                throw ValidationException::withMessages(['ids' => 'Assigned reports must be resolved before deletion.']);
                            }
                            $folder = ArchiveFolder::firstOrCreate(['name' => 'Deleted Reports', 'type' => 'reports', 'is_system' => true], ['item_count' => 0]);
                            $ticket->forceFill(['is_deleted' => true, 'deleted_by' => $user->id, 'archive_folder_id' => $folder->id]);
                            $column = $user->role.'_deleted';
                            if (in_array($column, $ticket->getFillable(), true)) $ticket->setAttribute($column, true);
                            $ticket->save();
                            $folder->update(['item_count' => Report::where('archive_folder_id', $folder->id)->count()]);
                            ActivityLog::log('report_deleted', 'Report moved to deleted: '.$ticket->title, $ticket->id, 'report');
                            return;
                        }
                        $response = app(ConcernController::class)->softDelete($request, $id);
                        $data = $response instanceof JsonResponse ? $response->getData(true) : [];
                        if (($data['success'] ?? false) !== true) {
                            throw ValidationException::withMessages(['ids' => $data['error'] ?? 'The selected ticket could not be deleted.']);
                        }
                        return;
                    }
                    if ($resource === 'concerns') {
                        // A linked reporter may remove their link, but cannot destroy the original ticket.
                        abort_unless($ticket->user_id === $user->id, 403);
                    }
                    $folder = $ticket->archive_folder_id ? ArchiveFolder::find($ticket->archive_folder_id) : null;
                    if ($action === 'restore-deleted') {
                        $ticket->forceFill(['is_deleted' => false, 'deleted_at' => null, 'deleted_by' => null, 'archive_folder_id' => null]);
                        $column = $user->role.'_deleted';
                        if (in_array($column, $ticket->getFillable(), true)) {
                            $ticket->setAttribute($column, false);
                        }
                        $ticket->save();
                        $type = $resource === 'reports' ? 'report' : 'concern';
                        ActivityLog::log($type.'_restored_deleted', 'Restored deleted ticket: '.$ticket->title, $ticket->id, $type);
                    } elseif ($resource === 'reports') {
                        ActivityLog::log('report_permanent_delete', 'Permanently deleted report: '.$ticket->title);
                        $ticket->forceDelete();
                    } else {
                        $response = app(ConcernController::class)->permanentDelete($request, $id);
                        if (($response->getData(true)['success'] ?? false) !== true) {
                            throw ValidationException::withMessages(['ids' => 'The concern could not be permanently deleted.']);
                        }
                    }
                    if ($folder) {
                        $model = $resource === 'reports' ? Report::class : Concern::class;
                        $folder->update(['item_count' => $model::where('archive_folder_id', $folder->id)->count()]);
                    }
                });
                $count++;
            } catch (ValidationException $exception) {
                $errors[] = collect($exception->errors())->flatten()->first();
            } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
                $errors[] = 'You cannot change one or more selected tickets.';
            } catch (\Throwable $exception) {
                report($exception);
                $errors[] = 'A selected ticket could not be updated. Please try again.';
            }
        }
        $skipped = count($errors);
        $verb = match ($action) {
            'archive' => 'archived', 'restore', 'restore-deleted' => 'restored',
            'soft-delete' => 'moved to deleted', 'permanent-delete' => 'permanently deleted',
        };
        $message = "{$count} {$resource} {$verb}.".($skipped ? " {$skipped} skipped. ".$errors[0] : '');
        return response()->json(['success' => $count > 0, 'count' => $count, 'skipped' => $skipped, 'message' => $message], $count > 0 ? 200 : 422);
    }
}
