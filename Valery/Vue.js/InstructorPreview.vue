<script>
export default {
  props: {
    facility: { type: Object },
    instructor: { type: Object }
  }
};
</script>

<template>
  <router-link
    :to="{ name: 'portal.object.instructor', params: { secondId: facility.second_id, instructorId: instructor.id } }"
  >
    <div class="instructor-preview team-1">
      <div class="team-photo">
        <a>
          <img
            :src="instructor.file ? `${$clientUrl}/attachment?path=/public/${instructor.file}&size=W_255_H_212_COVER` : '/img/empty-avatar.png'"
          />
        </a>
      </div>
      <div class="team-details">
        <h5>
          <a href="user-pofile.html">{{ instructor.name }}</a>
        </h5>
        <p
          v-html="$sanitize(instructor.description ? instructor.description.replace(/(?:\r\n|\r|\n)/g, '<br>') : '')"
        ></p>
        <p
          v-html="$sanitize(instructor.achievements ? instructor.achievements.replace(/(?:\r\n|\r|\n)/g, '<br>') : '')"
        ></p>
      </div>
    </div>
  </router-link>
</template>

<style lang="scss" scoped>
.instructor-preview {
  .img-fluid {
    width: 100%;
    height: 212px;
    background-size: cover !important;
  }

  .team-details {
    min-height: 200px;

    h5 {
      font-size: 16px;
      width: 100%;
    }

    p {
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 7;
    }
  }
}
</style>
