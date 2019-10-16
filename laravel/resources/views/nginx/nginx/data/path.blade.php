<form action = '' method = 'post'>
    {{ csrf_field() }}
    <input type="hidden" name="type" value="submit">
    <input type="text" name="pre_path"><br>
    <input type="submit" value="提交">
</form>