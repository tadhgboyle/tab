@foreach($timeline as $entry)
    <article class="media">
        <figure class="media-left">
            {{ $entry->emoji }}
        </figure>
        <div class="media-content">
            <div class="content">
                @if($entry->actor)
                    <strong>{{ $entry->actor->full_name }}</strong>
                    <br />
                @endif
                <span>{{ $entry->description }} • <small title="{{ $entry->time->format('M jS Y h:ia') }}">{{ $entry->time->diffForHumans() }}</small></span>
            </div>
        </div>
    </article>
@endforeach
