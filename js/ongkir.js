const formOngkir = document.getElementById("formOngkir");

const jarakInput = document.getElementById("jarak");

const tarifPerKmElement = document.getElementById("tarifPerKm");
const tarifMinimalElement = document.getElementById("tarifMinimal");

const totalOngkirElement = document.getElementById("totalOngkir");


// Tarif dari database
const tarifPerKm = 5000;
const tarifMinimal = 10000;


// Tampilkan tarif

tarifPerKmElement.textContent =
    "Rp " + tarifPerKm.toLocaleString("id-ID");

tarifMinimalElement.textContent =
    "Rp " + tarifMinimal.toLocaleString("id-ID");


// Hitung ongkir

formOngkir.addEventListener("submit", function (event) {

    event.preventDefault();

    const jarak = parseFloat(jarakInput.value);

    if (!jarak || jarak <= 0) {

        alert("Masukkan jarak pengiriman terlebih dahulu.");

        return;
    }


    let total = jarak * tarifPerKm;


    if (total < tarifMinimal) {
        total = tarifMinimal;
    }


    totalOngkirElement.textContent =
        "Rp " + total.toLocaleString("id-ID");

});