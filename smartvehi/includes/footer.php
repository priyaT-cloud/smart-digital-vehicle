<?php // includes/footer.php ?>
</div><!-- /.wrap -->
<script>
document.querySelectorAll('.alert').forEach(el=>{
  setTimeout(()=>{el.style.transition='opacity .5s';el.style.opacity='0';
  setTimeout(()=>el.remove(),500);},4500);
});
</script>
</body></html>
