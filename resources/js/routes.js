import NotFound from "./components/NotFound";
import Home from "./components/Home";

const routes = [
    {
        path: "/",
        component: Home,
        name: "Home",
    },
    {
        path: "*",
        component: NotFound,
        name: "NotFound",
    }
]

export default routes;
