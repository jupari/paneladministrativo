function toggleLoadingScreen(isLoading) {
    const loadingScreen = document.getElementById('loadingScreen');
    if (isLoading) {
        loadingScreen.classList.remove('d-none');
    } else {
        loadingScreen.classList.add('d-none');
    }
}
