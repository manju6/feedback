<template>
 <div class="container">
  <div class="create-post">
    <div class="rating-wrapper">
      <label class="rating-label">How helpful was this?
        <div class="ratingItemList" bgcolor="#000">
          <input class="rating rating-1" v-model="rating" id="rating-1-2" type="radio" v-bind:value="'1'" name="rating"/>
          <label class="rating rating-1" for="rating-1-2"><i class="em em-angry"></i></label>
          <input class="rating rating-2" v-model="rating" id="rating-2-2" type="radio" v-bind:value="'2'" name="rating"/>
          <label class="rating rating-2" for="rating-2-2"><i class="em em-disappointed"></i></label>
          <input class="rating rating-3" v-model="rating" id="rating-3-2" type="radio" v-bind:value="'3'" name="rating"/>
          <label class="rating rating-3" for="rating-3-2"><i class="em em-expressionless"></i></label>
          <input class="rating rating-4" v-model="rating" id="rating-4-2" type="radio" v-bind:value="'4'" name="rating"/>
          <label class="rating rating-4" for="rating-4-2"><i class="em em-grinning"></i></label>
          <input class="rating rating-5" v-model="rating" id="rating-5-2" type="radio" v-bind:value="'5'" name="rating" checked/>
          <label class="rating rating-5" for="rating-5-2"><i class="em em-heart_eyes"></i></label>
        </div>
      </label>
      <div class="feedback">
        <textarea v-model="text" placeholder="What can we do to improve?"></textarea>
        <button class="btn-success" v-on:click="createPost">Feedback Us !!</button>
      </div>
    </div>
  </div>

  <hr/>
     <p class="error" v-if="error"> {{ error }} </p>
     <div class="post-container">
        <p class="text-muted"> Comments </p>
        <div class="post" 
          v-for="(post,index) in posts" 
          v-bind:item="post" 
          v-bind:index="index" 
          v-bind:key="post._id"
          v-on:dblclick="deletePost(post._id)">
            <p class="text"> 
              Rating {{ post.rating }} : {{ `${post.createdAt.getDate()}/${post.createdAt.getMonth()}/${post.createdAt.getFullYear()}` }} :  {{ post.text }}  
            </p>
         </div>
     </div>
 </div>


</template> 

<script>
import PostService from '../PostService'
export default {
  name: 'PostComponent',
  data(){
    return {
      posts : [],
      error : '',
      text : '',
      rating:''
    }
  },
  async created(){
    try{
          this.posts = await PostService.getPosts();
    }
    catch(err){
      this.error =  err.message;
    }
  },
  methods:{
    async createPost(){
      await PostService.insertPost(this.text,this.rating);
      this.posts = await PostService.getPosts();
    },
    async deletePost(id){
      await PostService.deletePost(id);
      this.posts = await PostService.getPosts();
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
@import 'https://afeld.github.io/emoji-css/emoji.css';
@import url("https://fonts.googleapis.com/css?family=Lato:400,700");
body {
  font-family: 'Lato', Arial, sans-serif;
  font-size: 18px;
  background: #edf0f5;
  color: #202125;
}

ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}

.rating-wrapper {
  max-width: 400px;
  margin: 80px auto;
  background: #fff;
  padding: 0.5em;
  border-radius: 3px;
  box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
}
.rating-wrapper .rating-label {
  text-align: center;
  font-weight: 700;
  display: block;
}
.rating-wrapper .ratingItemList {
  max-width: 300px;
  margin: auto;
  display: flex;
  justify-content: space-between;
  padding: 1em 0;
}
.rating-wrapper input.rating {
  display: none;
}
.rating-wrapper label.rating {
  padding: 5px 3px;
  font-size: 32px;
  opacity: .7;
  -webkit-filter: grayscale(1);
          filter: grayscale(1);
  cursor: pointer;
}
.rating-wrapper label.rating:hover {
  -webkit-filter: grayscale(0.84);
          filter: grayscale(0.84);
  -webkit-transform: scale(1.1);
          transform: scale(1.1);
  transition: 100ms ease;
}
.rating-wrapper input.rating:checked + label.rating {
  -webkit-filter: grayscale(0);
          filter: grayscale(0);
  opacity: 1;
  -webkit-transform: scale(1.1);
          transform: scale(1.1);
}

.feedback {
  width: 100%;
  
}
.feedback textarea, .feedback input {
  max-width: 300px;
  width: 100%;

  margin: .5em auto;
  padding: .5em;
  font-family: 'Lato', sans-serif;
  border: 1px solid #d2d3d8;
  border-radius: 3px;
}
.feedback textarea:focus, .feedback textarea:active, .feedback input:focus, .feedback input:active {
  border-color: #3870c4;
  box-shadow: 0px 0px 1px 1px #3870c4;
  transition: 100ms;
}
.feedback textarea {
  height: 100px;
}
.feedback button {
  margin: 1em auto;
  display: table;
  text-align: center;
}

.disputelab_logo {
  width: 140px;
  position: absolute;
  top: 1em;
  left: 50%;
  margin-left: -70px;
}

button {
  color: #edeef0;
  background-color: #9b9ea9;
  border-radius: 3px;
  font-family: 'Lato', Arial sans-serif;
  border: 0;
  padding: 9px 15px;
  font-size: 15px;
}

button.not-disabled {
  color: white;
  background-color: #3870c4;
  text-shadow: 0px 1px 1px #214375;
  cursor: pointer;
}
button.not-disabled:hover {
  background-color: #2d599c;
  transition: 100ms;
}


</style>
