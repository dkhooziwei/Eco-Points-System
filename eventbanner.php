    <style>
        .carousel {
            width: 450px;
            height: 250px;
            margin: auto;
            overflow: hidden; /* hide scroll bar */

            padding: 5px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px #a6b357ff;
        }

        .carousel-container {
            position: relative;
            width: 600px;
            margin: auto;
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #C1D95C;
            color: black;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 0 5px #90d95cff;
        }

        .carousel-btn:hover {
            background-color: #a2b74dff;
        }

        .carousel-btn.left {
            left: 10px;
        }

        .carousel-btn.right {
            right: 10px;
        }

        .carousel-track{
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .slide{
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            padding: 10px;
        }

        .slide img{
            max-width: 420px;
            max-height: 350px;
            width: auto;
            height: auto;
            object-fit: cover;
            border-radius: 15px;
            display: block;

        }

        #event2 {
            width: 90%;
            height: auto;
        }

        #event3 {
            width: 92%;
            height: auto;
        }

        @media (max-width: 600px){

            .carousel-container {
                width: 100%;
                max-width: 360px;
                margin: 0 auto;
            }

            .carousel {
                width: 100%;
                height: 180px;
            }

            .slide {
                width: 100%;
                height: 100%;
                justify-content: center;
            }

            .slide img{
                max-width: 90%;
                max-height: 180px;
                width: auto;
                height: auto;
                border-radius: 15px;
            }

            #event2 {
                width: 80%;
                height: auto;
            }

            #event3 {
                width: 85%;
                height: auto;
            }

            .carousel-btn {
                width: 28px;
                height: 28px;
                font-size: 18px;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){

            .carousel-container {
                width: 680px;
                margin: auto;
            }

            .carousel {
                width: 520px;
                height: 280px;
            }

            .slide img {
                max-width: 500px;
                max-height: 280px;
            }
        }
    </style>

<br>
<div class="carousel-container">
    <button class="carousel-btn left" onclick="scroll_carousel(-1)"><</button>
    <div class="carousel">
        <div class="carousel-track">
            <div class="slide"><img src="../images/Event1.webp" alt="Event 1"></div>
            <div class="slide"><img src="../images/Event2.jpg" alt="Event 2" id="event2"></div>
            <div class="slide"><img src="../images/Event3.png" alt="Event 3" id="event3"></div>
        </div>
    </div>
    <button class="carousel-btn right" onclick="scroll_carousel(1)">></button>
</div>

<script>
    let currentIndex = 0;

    function scroll_carousel(direction){
        const track = document.querySelector(".carousel-track");
        const slides = document.querySelectorAll(".slide");
        const slideWidth = slides[0].offsetWidth; // Get width of one slide
        
        // Update index
        currentIndex += direction;

        // Prevent going out of bounds
        if(currentIndex < 0) currentIndex = 0;
        if(currentIndex > slides.length - 1) currentIndex = slides.length - 1;

        // Move the track using Transform (matches your CSS transition)
        track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
    }
</script>