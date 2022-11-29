@extends('layouts.app')

@section('content')
<div class="container">
    <div class="controls">
        @if ($parent_dir_id)
        <div class="back-arrow">
            <a href="{{route('showdir', ['id' => $parent_dir_id])}}">back</a>
        </div>
        @endif
        <a href="javascript:void(0)" data-toggle="modal" data-target="#myModal">new folder</a>
            <div class="modal" id="myModal">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">Make new folder</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <!-- Modal body -->
                        <div class="container">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-10">
                                        {{ Form::open(['method' => 'post', 'route' => ['createdir'], 'id' => 'new-folder']) }}
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon3">{{$path_str}}</span>
                                            </div>
                                            {{ Form::text('name', '', ['class' => 'form-control', 'aria-describedby' => 'basic-addon3', 'autocomplete' => 'off']) }}
                                        </div>
                                        {{ Form::hidden('parent_id', $current_dir_id) }}
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="submit" form="new-folder" class="btn btn-success">Make</button>
                        </div>

                    </div>
                </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="path">
                Path:
                @foreach ($folders as $folder)
                    @if (!$loop->last)
                        <a ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)" href="{{route('showdir', ['id'=> $folder->id])}}" dir_id="{{$folder->id}}">{{$folder->name}}</a>\
                    @else
                        <span dir_id="{{$folder->id}}">{{$folder->name}}</span>
                    @endif
                @endforeach
            </div>
            @foreach ($childrens as $child)
                <ul>
                    <li>
                        <div class="folder" >
                            <span ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)"><img src="/img/folder-invoices.png"  dir_id="{{$child->id}}"><a dir_id="{{$child->id}}" href="{{route('showdir', ['id'=> $child->id])}}">{{$child->name}}</a></span>
                            <img src="/img/delete-sign.png" class="deletedir" dir_id="{{$child->id}}" style="width: 5%; height: 3%">
                        </div>
                    </li>
                </ul>
            @endforeach
            @foreach ($file_images as $file_image)
                <ul>
                    <li>
                        <div class="file" ondrag="drag(event)" ondragstart="drag(event)">
                            <img src="/img/document.png" file_id="{{$file_image->id}}" >{{$file_image->doc->name}}</span>
                        </div>
                    </li>
                </ul>
            @endforeach
        </div>
    </div>
</div>
@endsection
@push('js')
        <script src="{{ asset('js/jquery-1.9.1.min.js')}}"></script>
        <script>
        function allowDrop(event) {
            event.target.style.background = "yellow";
            event.target.style.border = "1px solid black";

            event.preventDefault();
        }

        function drag(event) {
            event.dataTransfer.setData("text/html", event.target.getAttribute('file_id'));
        }

        function dragLeave(event) {
            event.target.style.background = "";
            event.target.style.border = "";
            event.preventDefault();
        }

        function drop(event) {
            console.log('folder id: ' + event.target.getAttribute('dir_id'));
            var data = event.dataTransfer.getData('text/html');
            console.log('file id: ' + data);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            let dir_id = event.target.getAttribute('dir_id');
            let file_id = event.dataTransfer.getData('text/html');
            if (!dir_id || !file_id) {
                return;
            }
            $.ajax({
                type: "POST",
                url: '{{ route('movefile') }}',
                data: {
                    dir_id: dir_id,
                    file_id: file_id
                },
                success: function (data) {
                    location.reload();
                    console.log(data);
                },
                error: function (data, textStatus, errorThrown) {
                    console.log(data, textStatus, errorThrown);
                },
            })
        }
        $(document).ready(function () {
            $('.deletedir').on('click', function () {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                let dir_id = $(this).attr('dir_id');
                $.ajax({
                    type: "POST",
                    url: '{{ route('deletedir') }}',
                    data: {
                        dir_id: dir_id,
                    },
                    success: function (data) {
                        console.log(data);
                        if (data['result'] == 'success') {
                            location.reload();
                        } else if (data['result'] == 'error') {
                            alert(data['msg']);
                        }
                    },
                    error: function (data, textStatus, errorThrown) {
                        console.log(data, textStatus, errorThrown);
                    },
                })
            });
        });
    </script>
@endpush
