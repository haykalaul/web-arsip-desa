@extends('layout.main')

@section('content')
    <x-breadcrumb
        :values="[__('menu.agenda.menu'), __('menu.agenda.outgoing_letter')]">
    </x-breadcrumb>

    <div class="card mb-5">
        <div class="card-header">
            <form action="{{ url()->current() }}">
                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                <div class="row">
                    <div class="col">
                        <x-input-form name="since" :label="__('menu.agenda.start_date')" type="date"
                                      :value="$since ? date('Y-m-d', strtotime($since)) : ''"/>
                    </div>
                    <div class="col">
                        <x-input-form name="until" :label="__('menu.agenda.end_date')" type="date"
                                      :value="$until ? date('Y-m-d', strtotime($until)) : ''"/>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="filter" class="form-label">{{ __('menu.agenda.filter_by') }}</label>
                            <select class="form-select" id="filter" name="filter">
                                <option
                                    value="letter_date" @selected(old('filter', $filter) == 'letter_date')>{{ __('model.letter.letter_date') }}</option>
                                <option
                                    value="created_at" @selected(old('filter', $filter) == 'created_at')>{{ __('model.general.created_at') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label class="form-label">{{ __('menu.general.action') }}</label>
                            <div class="row">
                                <div class="col">
                                    <button class="btn btn-primary"
                                            type="submit">{{ __('menu.general.filter') }}</button>
                                    <a
                                        href="{{ route('agenda.outgoing.print') . '?' . $query }}"
                                        target="_blank"
                                        class="btn btn-primary">
                                        {{ __('menu.general.print') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('model.letter.agenda_number') }}</th>
                    <th>{{ __('model.letter.classification_code') }}</th>
                    <th>{{ __('model.letter.reference_number') }}</th>
                    <th>{{ __('model.letter.to') }}</th>
                    <th>{{ __('model.letter.letter_date') }}</th>
                </tr>
                </thead>
                @if($data && $data->count() > 0)
                    <tbody>
                    @foreach($data as $agenda)
                        <tr>
                            <td>
                                <i class="fab fa-angular fa-lg text-danger me-3"></i>
                                <strong>{{ $agenda->agenda_number }}</strong>
                            </td>
                            <td>
                                @if($agenda->classification)
                                    <span class="badge bg-primary">{{ $agenda->classification->code }}</span>
                                    <small class="text-muted d-block">{{ $agenda->classification->name ?? '' }}</small>
                                @else
                                    <span class="text-muted">{{ $agenda->classification_code ?? '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('transaction.outgoing.show', $agenda) }}" class="text-primary">
                                    {{ $agenda->reference_number }}
                                </a>
                            </td>
                            <td>{{ $agenda->to }}</td>
                            <td>
                                <span>{{ $agenda->formatted_letter_date }}</span>
                                <small class="text-muted d-block">{{ $agenda->letter_date->format('d/m/Y') }}</small>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                @else
                    <tbody>
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">{{ __('menu.general.empty') }}</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                @endif
                <tfoot class="table-border-bottom-0">
                <tr>
                    <th>{{ __('model.letter.agenda_number') }}</th>
                    <th>{{ __('model.letter.classification_code') }}</th>
                    <th>{{ __('model.letter.reference_number') }}</th>
                    <th>{{ __('model.letter.to') }}</th>
                    <th>{{ __('model.letter.letter_date') }}</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($data && $data->hasPages())
        <div class="d-flex justify-content-center">
            {!! $data->appends(['search' => $search, 'since' => $since, 'until' => $until, 'filter' => $filter])->links() !!}
        </div>
    @endif
@endsection
