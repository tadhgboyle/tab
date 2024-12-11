@foreach($categories as $category)
    <div class="field mb-4">
        <label class="label">{{ $category['name'] }} Limit</label>
        
        <div class="columns">
            <!-- Limit Input -->
            <div class="column is-half">
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="limits[{{ $category['id'] }}]" class="input money-input" placeholder="Limit" 
                        value="{{ isset($user) ? $category['limit']->limit->formatForInput() : '' }}">
                </div>
            </div>

            <!-- Duration Dropdown -->
            <div class="column is-half">
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="durations[{{ $category['id'] }}]">
                            <option value="{{ \App\Enums\UserLimitDuration::Daily->value }}" @if((isset($user) && $category['limit']->duration === \App\Enums\UserLimitDuration::Daily) || !isset($user)) selected @endif>
                                Daily
                            </option>
                            <option value="{{ \App\Enums\UserLimitDuration::Weekly->value }}" @if(isset($user) && $category['limit']->duration === \App\Enums\UserLimitDuration::Weekly) selected @endif>
                                Weekly
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
