<x-app-layout>
            

<div style="background:red;color:white;padding:30px;font-size:40px">
TEST PROFILE
</div>
<div
                class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden mb-8 animate-fadeUp">
                <div class="h-48 sm:h-64 bg-gradient-to-r from-primary via-[#003366] to-primary relative">
                    <div class="absolute inset-0 opacity-20 cubes-pattern"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                </div>
                <div class="px-6 sm:px-12 pb-10">
                    <div
                        class="flex flex-col md:flex-row items-center md:items-end justify-between -mt-20 sm:-mt-24 mb-10 gap-6">
                        <div
                            class="flex flex-col md:flex-row items-center md:items-end gap-6 text-center md:text-right">
                            <div class="relative group">
                                <img id="profile-big-img"
                                    src="https://ui-avatars.com/api/?name=Ahmed+Mohamed&background=001f3f&color=fff"
                                    class="w-32 h-32 md:w-44 md:h-44 rounded-full border-4 md:border-8 border-white shadow-2xl object-cover bg-white transition-transform duration-500 group-hover:scale-105">
                                <div
                                    class="absolute bottom-2 left-2 w-6 h-6 bg-green-500 border-4 border-white rounded-full shadow-lg">
                                </div>
                            </div>
                            <div class="pb-2 relative md:top-8 top-4">
                                <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-3" id="profile-big-name">
                                   {{ auth()->user()->name }}</h1>
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                                    <span
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-accent/10 text-accent font-bold rounded-xl text-sm border border-accent/20">
                                        <i class="fas fa-user-shield"></i> <span id="profile-big-role">مدير
                                            النظام</span>
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary/5 text-primary font-bold rounded-xl text-sm border border-primary/10">
                                        <i class="fas fa-check-circle"></i> نشط الآن
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <a href="{{ route('profile') }}"
                                class="inline-flex items-center gap-3 px-6 py-3 bg-white border-2 border-slate-100 text-primary font-black rounded-2xl hover:border-accent hover:text-accent transition-all shadow-sm hover:shadow-md">
                                <i class="fas fa-user-edit text-accent"></i> تعديل البيانات
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100 hover:border-accent/20 transition-all group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-primary shadow-sm group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fas fa-envelope text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-bold mb-1 uppercase tracking-wider">البريد
                                        الإلكتروني</p>
                                    <p class="font-bold text-slate-700 font-sans" id="profile-email"> {{ auth()->user()->email }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100 hover:border-accent/20 transition-all group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-primary shadow-sm group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fas fa-phone-alt text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-bold mb-1 uppercase tracking-wider">رقم الهاتف
                                    </p>
                                    <p class="font-bold text-slate-700 font-sans" dir="ltr" id="profile-phone">
                                        01012345678</p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100 hover:border-accent/20 transition-all group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-primary shadow-sm group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fas fa-calendar-alt text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-bold mb-1 uppercase tracking-wider">تاريخ
                                        الانضمام</p>
                                    <p class="font-bold text-slate-700">يناير 2024</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 animate-fadeUp">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-center">
                    <div class="text-4xl font-black text-primary mb-1">12</div>
                    <p class="text-sm text-slate-500 font-medium">إجمالي البلاغات</p>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-center">
                    <div class="text-4xl font-black text-green-600 mb-1">8</div>
                    <p class="text-sm text-slate-500 font-medium">بلاغات منجزة</p>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-center">
                    <div class="text-4xl font-black text-orange-500 mb-1">3</div>
                    <p class="text-sm text-slate-500 font-medium">قيد التنفيذ</p>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-center">
                    <div class="text-4xl font-black text-accent mb-1">24</div>
                    <p class="text-sm text-slate-500 font-medium">إشارات مسجلة</p>
                </div>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden animate-fadeUp">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-xl font-black text-primary flex items-center gap-3">
                        <span class="w-9 h-9 bg-primary/10 rounded-xl flex items-center justify-center text-primary"><i
                                class="fas fa-file-alt"></i></span>
                        آخر البلاغات
                    </h2>
                    <a href="../reports/reports.html" class="text-accent font-bold text-sm hover:underline">عرض الكل</a>
                </div>
                <div class="divide-y divide-slate-50" id="profile-reports-list"></div>
            </div>
</x-app-layout>
