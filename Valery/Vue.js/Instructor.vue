<script>
import axios from "axios";

import Navbar from "~/components/Navbar";
import Footer from "~/components/Footer";
import Spinner from "~/components/global/Spinner";

export default {
  components: {
    Navbar,
    Footer,
    Spinner
  },

  layout: "simple",

  async mounted() {},

  head() {
    return { title: this.$t("instructor") };
  },

  async asyncData({ route, params, redirect }) {
    const result = await axios(
      `/facility/object/${route.params.secondId}/instructor/${route.params.instructorId}`
    );
    if (result.data.status === "success") {
      let data = {
        facility: result.data.facility,
        instructor: result.data.instructor
      };
      data.facility.sports.forEach(
        sport =>
          (sport.params = data.facility.sport_params.filter(
            x => x.pivot.sport_id === sport.id
          ))
      );
      return data;
    } else {
      redirect("/404");
    }
  }
};
</script>

<template>
  <div class="instructor">
    <Navbar />

    <div v-if="!instructor" class="overview-bgi listing-banner default">
      <div class="container listing-banner-info"></div>
    </div>

    <div v-if="instructor" class="banner" id="banner">
      <div id="bannerCarousole" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item banner-max-height active">
            <img
              class="d-block w-100 h-100"
              :src="`${facility.photo ? `${$clientUrl}/attachment?path=/public/${facility.photo}&size=W_1920_H_550_COVER` : '/img/empty-avatar.png'}`"
              alt="banner"
            />
            <div class="carousel-caption banner-slider-inner d-flex text-center"></div>
          </div>
        </div>
      </div>
      <div class="banner-inner-2">
        <div class="container">
          <div class="breadcrumb-area">
            <h1>{{ instructor.name }}</h1>
            <ul class="breadcrumbs">
              <li>
                <router-link
                  :to="{ name: 'portal.object', params: { secondId: facility.second_id } }"
                >{{ facility.name }}</router-link>
              </li>
              <li class="active">
                <router-link
                  :to="{ name: 'portal.object.instructors', params: { secondId: facility.second_id } }"
                >Наша команда</router-link>
              </li>
              <li class="active">{{ instructor.name }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div v-if="instructor" class="user-profile content-area">
      <div class="container">
        <h3 class="heading-2">Профиль</h3>
        <div class="row">
          <div class="col-lg-8">
            <div class="row team-2">
              <div class="col-xl-5 col-lg-6 col-md-5 col-sm-12 col-pad">
                <div class="photo">
                  <div
                    alt="avatar-9"
                    class="img-fluid"
                    :style="{ 'background-image': `url(${instructor.file ? `${$clientUrl}/attachment?path=/public/${instructor.file}&size=W_303_H_363_COVER` : '/img/empty-avatar.png'})` }"
                  />
                </div>
              </div>
              <div class="col-xl-7 col-lg-6 col-md-7 col-sm-12 col-pad align-self-center bg">
                <div class="detail">
                  <h4>
                    <a href="#">{{ instructor.name }}</a>
                  </h4>

                  <p v-if="instructor.language" style="margin-bottom:10px;">
                    <i class="fa fa-globe" style="margin-right:5px;"></i>
                    {{ instructor.language }}
                  </p>

                  <h5 class="heading-2">Достижения</h5>
                  <p
                    v-html="$sanitize(instructor.achievements ? instructor.achievements.replace(/(?:\r\n|\r|\n)/g, '<br>') : '')"
                  ></p>
                </div>
              </div>
            </div>
            <div class="agent-biography">
              <h3 class="heading-2">О себе</h3>
              <p
                v-html="$sanitize(instructor.description ? instructor.description.replace(/(?:\r\n|\r|\n)/g, '<br>') : '')"
              ></p>
            </div>
          </div>

          <div class="col-lg-4 col-md-12">
            <div class="sidebar-right">
              <div class="widget booking-now d-none d-xl-block d-lg-block">
                <h3 class="sidebar-title">Виды спорта</h3>
                <div class="s-border"></div>
                <div v-for="sport in facility.sports" v-bind:key="sport.id" class="sport">
                  <div class="name">
                    <h5>{{ sport.name }}</h5>
                  </div>
                  <ul>
                    <li v-for="sportParam in sport.params" v-bind:key="sportParam.id">
                      {{ sportParam.name }} :
                      <span
                        v-if="sportParam.type !== 'checkbox'"
                      >{{ sportParam.pivot.value }}</span>
                      <span v-else-if="sportParam.type === 'checkbox'">
                        <i v-if="sportParam.pivot.value === '1'" class="fa fa-check-square-o"></i>
                        <i v-else class="fa fa-square-o"></i>
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="widget opening-hours" v-if="facility.work_time">
                <h3 class="sidebar-title">Режим работы</h3>
                <div class="s-border"></div>
                <p
                  v-html="$sanitize(facility.work_time ? facility.work_time.replace(/(?:\r\n|\r|\n)/g, '<br>') : '')"
                ></p>
              </div>
              <div class="widget recent-listing" v-if="facility.phone || facility.email">
                <h3 class="sidebar-title">Контактная информация</h3>
                <div class="s-border"></div>
                <div class="media mb-4">
                  <div class="media-body align-self-center">
                    <h5 v-if="facility.phone">
                      <a :href="`tel:${facility.phone}`">{{ facility.phone }}</a>
                    </h5>
                    <h5 v-if="facility.email">
                      <a :href="`mailto:${facility.email}`">{{ facility.email }}</a>
                    </h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- User profile page end -->

    <Spinner v-if="!instructor" minHeight="600px" />

    <Footer></Footer>
  </div>
</template>

<style lang="scss" scope>
.instructor {
  width: 100%;

  .overview-bgi.listing-banner.default {
    background-image: none;
    height: 80px;
    background: #ccc;
    opacity: 0.5;

    @media (max-width: 992px) {
      display: none;
    }
  }

  .banner h1 {
    color: white !important;
    margin: 0 0 15px;
    font-weight: 700;
    color: #fff;
    font-size: 30px;
  }

  .banner p {
    display: inline-block;
    list-style: none;
    font-size: 16px;
    font-weight: 500;
  }

  .carousel-item.banner-max-height {
    max-height: 355px;
  }

  .img-fluid {
    width: 100%;
    height: 363px;
    background-size: cover;
  }

  .sidebar-right {
    input,
    textarea {
      margin-left: 0;
    }
  }

  .breadcrumb-area {
    letter-spacing: 1px;
    text-align: center;
    width: 100%;
    position: absolute;
    top: 50%;
    right: 0;
    left: 0;
    margin: 0;
    padding: 0;
    list-style: none;

    li {
      display: inline-block;
      list-style: none;
      font-size: 16px;
      font-weight: 500;
      color: white;

      a {
        color: white;

        &:hover {
          text-decoration: underline;
        }
      }
    }

    .active::before {
      content: "\f105";
      font-family: "FontAwesome";
      font-size: 14px;
      margin-right: 7px;
      font-weight: 600;
    }
  }

  h5.heading-2 {
    font-size: 20px;
    margin-bottom: 0px;
  }

  .sport {
    .name h5 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 15px;
      color: #4d4d4d;
    }

    ul {
      margin: 0;
      padding: 0;
      list-style: none;
      color: #535353;
      margin-bottom: 20px;

      li {
        font-weight: 600;
        color: #50596e;
        line-height: 30px;
        font-size: 14px;

        span {
          font-weight: 500;
          color: #737780;
        }
      }
    }
  }
}
</style>
