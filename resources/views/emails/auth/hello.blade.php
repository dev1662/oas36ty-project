{{-- {{$message}} --}}
{{-- <h4>Hi,</h4><br/> --}}
<div>{!! $template_data['body'] !!}</div>
{{-- <br/>
    
<p>Have a great day!</p> --}}
{{-- <p>{{ config('app.name') }}</p> --}}
<br/>
<div>
    {{-- {{$template_data['files']}}
     --}}
     <?php 
     
     if($template_data['files']){
        foreach ($template_data['files'] as $key => $file) {
            echo '<br>'.'<a  href="'.$file.'" download>
                <img src="'.$file.'" alt="W3Schools" width="104" height="142">

                </a>';
        }
     }
     ?>
    {{-- <a href=""></a> --}}
</div>