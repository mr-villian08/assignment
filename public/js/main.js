// ? ********************************************************** Create the buckets ********************************************************** */
const createBucket = async (event) => {
    event.preventDefault();
    try {
        const data = {
            name: $("#bucket-name").val(),
            volume: $("#bucket-volume").val(),
        };
        const result = await $.ajax({
            url: "/buckets",
            type: "POST",
            data: data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });

        if (result.status) {
            $("#bucket-form")[0].reset();
            return alert(result.message);
        }

        throw new Error(result.message);
    } catch (error) {
        alert(error.message);
    }
};

// ? ********************************************************** Create the balls ********************************************************** */
const showBalls = async () => {
    try {
        const result = await $.ajax({
            url: "/balls",
            type: "GET",
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });

        if (!result.status) {
            return alert(result.message);
        }

        let formHtml = "";
        const balls = result.data;
        if (balls.length > 0) {
            for (const key in balls) {
                formHtml += `
                <div class="form-group">
                    <label for="${balls[key].color}-ball">${balls[key].color} Balls: (size = ${balls[key].size})</label>
                    <input type="number" name="balls[${balls[key].color}]" id="${balls[key].color}-ball" class="form-control" required>
                </div>`;
            }

            formHtml += `<button type="submit" class="btn btn-primary mt-2" id="bucket-suggestion-button">Suggest Buckets</button>`;
        }
        return $("#bucket-suggestion-form").html(formHtml);
    } catch (error) {
        alert(error.message);
    }
};

// ? ********************************************************** Create the balls ********************************************************** */
const createBall = async (event) => {
    event.preventDefault();
    try {
        const data = {
            color: $("#ball-color").val(),
            size: $("#ball-size").val(),
        };

        const result = await $.ajax({
            url: "/balls",
            type: "POST",
            data: data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });

        if (result.status) {
            $("#ball-form")[0].reset();
            showBalls();
            return alert(result.message);
        }

        throw new Error(result.message);
    } catch (error) {
        alert(error.message);
    }
};

// ? ********************************************************** Show the result ********************************************************** */
const showBucketSuggestions = async () => {
    try {
        const result = await $.ajax({
            url: "/bucket-suggestions",
            type: "GET",
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });

        if (!result.status) {
            throw new Error(result.message);
        }
        let formHtml = `<ol class="list-group list-group-numbered"> <p>Following are the suggested buckets:</p>`;
        if (result.data.data.length > 0) {
            result.data.data.forEach((item) => {
                formHtml += `<li class="list-group-item">${item}</li>`;
            });
            formHtml += `</ol>`;
        }

        $("#result-body").html(formHtml);
    } catch (error) {}
};

// ? ********************************************************** Create bucket suggestion ********************************************************** */
const createBucketSuggestion = async (event) => {
    event.preventDefault();
    try {
        const data = new FormData($("#bucket-suggestion-form")[0]);
        const result = await $.ajax({
            url: "/bucket-suggestions",
            type: "POST",
            dataType: "json",
            async: true,
            processData: false,
            contentType: false,
            data: data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        if (result.status) {
            showBucketSuggestions();
            $("#bucket-suggestion-form")[0].reset();
            return alert(result.message);
        }

        throw new Error(result.message);
    } catch (error) {
        alert(error.message);
    }
};

// ? ********************************************************** Initial function ********************************************************** */
$(document).ready(() => {
    showBalls();
    showBucketSuggestions();
});
