<div class="row">
    <div class="col-12">
        <div id="{{$id}}" class="tab-list-menu">
            <ul>
                @foreach($data as $key => $value)
                    <li>
                        @if($value['function'] === true)
                            <button id="{{$value['id']}}" class="tab-list-menu-item {{($value['active']===true?'active':'')}}" onclick="scion.action.tab('{{$value['id']}}', {{$value['id']}}_func())" @if($value['disabled']===true) disabled @endif>
                                <span class="sc-icon"><i class="{{$value['icon']}}"></i></span> <span>{{$value['title']}}</span>
                            </button>
                        @else
                            <button id="{{$value['id']}}" class="tab-list-menu-item {{($value['active']===true?'active':'')}}" onclick="scion.action.tab('{{$value['id']}}')" @if($value['disabled']===true) disabled @endif>
                                <span class="sc-icon"><i class="{{$value['icon']}}"></i></span> <span>{{$value['title']}}</span>
                            </button>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>