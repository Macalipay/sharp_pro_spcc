@if($message = Session::get('success'))
<script>
	$(function(){
		toastr.success('{{ $message }}','Success');
	});
</script>
@endif

@if(count($errors) > 0 )
	@foreach ($errors->all() as $error)
		<script>
			$(function(){
				toastr.error('{{ $error }}','Failed');
			});
		</script>
	@endforeach
@endif
