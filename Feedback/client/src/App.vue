<template>
  <div id="app">
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
          <ul class="navbar-nav">
            <li class="nav-item active">
              <a class="nav-link" href="#"><strong>Node - JS </strong></a>
            </li>
          </ul>
        </nav>
     
    <PostComponent/>

    <div class="footer navbar-fixed-bottom">
      <footer class="main-footer">
        <strong> Bosch-2018 &copy; Integration Team.</strong> All rights reserved.
      </footer>
    </div>
  </div>
</template>

<script>
import PostComponent from './components/PostComponent.vue'

export default {
  name: 'app',
  components: {
    PostComponent
  }
}
</script>

<style>
#app {
   font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 4px;
}

.footer {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  text-align: center;
  height:30px;
  background-color: #000;
  color: #fff;
}

@import 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css';
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'



</style>
