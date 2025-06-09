import { router, useForm } from "@inertiajs/react";

export default function Login() {
    const { errors, setData, data, post, processing } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    return (
        <div className="flex min-full flex-col justify-center px-6 py-12 lg:px-8">
            <div className="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 className="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">
                    CarvaSys
                </h2>
            </div>
            <div className="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form
                    className="space-y-6"
                    onSubmit={(e) => {
                        e.preventDefault();
                        post("/login");
                    }}
                >
                    <div>
                        <label
                            htmlFor="email"
                            className="block text-sm/6  font-medium text-gray-900"
                        >
                            Email
                        </label>
                        <div className="mt-2">
                            <input
                                type="email"
                                name="email"
                                id="email"
                                autoComplete="email"
                                value={data.value}
                                onChange={(e) =>
                                    setData("email", e.target.value)
                                }
                                required
                                className="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                            />
                            {errors.email && (
                                <p className="text-red-500 tracking-tight font-serif">
                                    {errors.email}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <div className="flex items-center justify-between">
                            <label
                                htmlFor="password"
                                className="block text-sm/6 font-medium text-gray-900"
                            >
                                Senha
                            </label>
                            <div className="text-sm">
                                <a
                                    href="#"
                                    className="font-semibold text-indigo-600 hover:text-indigo-500"
                                >
                                    Esqueceu a senha?
                                </a>
                            </div>
                        </div>
                        <div className="mt-2">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                autoComplete="current-password"
                                value={data.password}
                                onChange={(e) =>
                                    setData("password", e.target.value)
                                }
                                required
                                className="block w-full rounded-md bg-white px-3  py-1.5  text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                            />
                            {errors.password && (
                                <p className="text-red-500 tracking-tight font-serif">
                                    {errors.password}
                                </p>
                            )}
                        </div>
                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                className="my-3 mr-2"
                                value={data.remember}
                                onChange={(e) =>
                                    setData("remember", e.target.checked)
                                }
                            />
                            <label className="block text-sm/6 font-medium text-gray-900">
                                Lembrar-me
                            </label>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex w-full justify-center rounded-md  bg-indigo-600 px-3 py-1.6 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-offset-2 focus-visible: outline-indigo-600 cursor-pointer"
                        >
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
